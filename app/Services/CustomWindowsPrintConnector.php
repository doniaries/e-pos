<?php

namespace App\Services;

use Exception;
use BadMethodCallException;
use Mike42\Escpos\PrintConnectors\PrintConnector;

/**
 * Custom Connector for sending print jobs to Windows printers.
 * Cloned from WindowsPrintConnector to fix REGEX_SMB limitation (missing '+').
 */
class CustomWindowsPrintConnector implements PrintConnector
{
    /**
     * @var array $buffer
     *  Accumulated lines of output for later use.
     */
    private $buffer;

    /**
     * @var string $hostname
     *  The hostname of the target machine, or null if this is a local connection.
     */
    private $hostname;

    /**
     * @var boolean $isLocal
     *  True if a port is being used directly (must be Windows), false if network shares will be used.
     */
    private $isLocal;

    /**
     * @var int $platform
     *  Platform we're running on.
     */
    private $platform;

    /**
     * @var string $printerName
     *  The name of the target printer.
     */
    private $printerName;

    /**
     * @var string $userName
     *  Login name for network printer.
     */
    private $userName;

    /**
     * @var string $userPassword
     *  Password for network printer.
     */
    private $userPassword;

    /**
     * @var string $workgroup
     *  Workgroup that the printer is located on.
     */
    private $workgroup;

    const PLATFORM_LINUX = 0;
    const PLATFORM_MAC = 1;
    const PLATFORM_WIN = 2;

    const REGEX_LOCAL = "/^(LPT\d|COM\d)$/";

    // Relaxed regex to allow '+' and other common chars in printer names
    const REGEX_PRINTERNAME = "/^[\d\w+\.\(\)\s-]+$/";

    // Relaxed regex for SMB to allow '+' etc.
    const REGEX_SMB = "/^smb:\/\/([\s\d\w-]+(:[\s\d\w+-]+)?@)?([\d\w-]+\.)*[\d\w-]+\/([\d\w-]+\/)?[\d\w+\.\(\)\s-]+$/";

    public function __construct($dest)
    {
        $this->platform = $this->getCurrentPlatform();
        $this->isLocal = false;
        $this->buffer = null;
        $this->userName = null;
        $this->userPassword = null;
        $this->workgroup = null;

        if (preg_match(self::REGEX_LOCAL, $dest) == 1) {
            if ($this->platform !== self::PLATFORM_WIN) {
                throw new BadMethodCallException("WindowsPrintConnector can only be used on Windows.");
            }
            $this->isLocal = true;
            $this->hostname = null;
            $this->printerName = $dest;
        } elseif (preg_match(self::REGEX_SMB, $dest) == 1) {
            $part = parse_url($dest);
            $this->hostname = $part['host'];
            $path = ltrim($part['path'], '/');
            if (strpos($path, "/") !== false) {
                $pathPart = explode("/", $path);
                $this->workgroup = $pathPart[0];
                $this->printerName = $pathPart[1];
            } else {
                $this->printerName = $path;
            }
            if (isset($part['user'])) {
                $this->userName = $part['user'];
                if (isset($part['pass'])) {
                    $this->userPassword = $part['pass'];
                }
            }
        } elseif (preg_match(self::REGEX_PRINTERNAME, $dest) == 1) {
            $hostname = gethostname();
            if (!$hostname) {
                $hostname = "localhost";
            }
            $this->hostname = $hostname;
            $this->printerName = $dest;
        } else {
            // Fallback: If verification fails, just try to use it blindly if it starts with smb://
            // likely catching cases our regex missed.
            if (str_starts_with($dest, 'smb://')) {
                // Parse manually if simple
                $part = parse_url($dest);
                $this->hostname = $part['host'] ?? 'localhost';
                $this->printerName = ltrim($part['path'] ?? '', '/');
            } else {
                // Allow "Invalid" names to pass if we are desperate, but standard behavior throws exception.
                // Let's assume our relaxed REGEX covers it.
                throw new BadMethodCallException("Printer '$dest' is not a valid printer name.");
            }
        }
        $this->buffer = [];
    }

    public function __destruct()
    {
        if ($this->buffer !== null) {
            trigger_error("Print connector was not finalized. Did you forget to close the printer?", E_USER_NOTICE);
        }
    }

    public function finalize()
    {
        $data = implode($this->buffer);
        $this->buffer = null;
        if ($this->platform == self::PLATFORM_WIN) {
            $this->finalizeWin($data);
        } else {
            // For simplicity, we only really support Windows logic here as per name
            $this->finalizeWin($data);
        }
    }

    public function read($len)
    {
        return false;
    }

    public function write($data)
    {
        $this->buffer[] = $data;
    }

    protected function finalizeWin($data)
    {
        if (!$this->isLocal) {
            $device = "\\\\" . $this->hostname . "\\" . $this->printerName;
            // ... (Simple copy for share)
            $filename = tempnam(sys_get_temp_dir(), "escpos");
            file_put_contents($filename, $data);
            if (!copy($filename, $device)) {
                unlink($filename);
                throw new Exception("Failed to copy file to printer at $device");
            }
            unlink($filename);
        } else {
            if (!file_put_contents($this->printerName, $data)) {
                throw new Exception("Failed to write to port " . $this->printerName);
            }
        }
    }

    protected function getCurrentPlatform()
    {
        if (PHP_OS == "WINNT") return self::PLATFORM_WIN;
        return self::PLATFORM_LINUX;
    }
}
