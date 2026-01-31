<x-filament-widgets::widget>
    <x-filament::section>
        <div
            class="flex items-center gap-x-3"
            x-data="{ 
                date: new Date(),
                time: '',
                day: '',
                init() {
                    this.updateTime();
                    setInterval(() => this.updateTime(), 1000);
                },
                updateTime() {
                    this.date = new Date();
                    
                    // Format Time: 14:30:45
                    this.time = this.date.toLocaleTimeString('id-ID', { 
                        hour: '2-digit', 
                        minute: '2-digit', 
                        second: '2-digit',
                        hour12: false
                    });

                    // Format Date: Senin, 15 Januari 2026
                    this.day = this.date.toLocaleDateString('id-ID', { 
                        weekday: 'long', 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    });
                }
            }">
            <div class="flex-1">
                <h2 class="text-3xl font-bold tracking-tight text-gray-950 dark:text-white" x-text="time">
                    --:--:--
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400" x-text="day">
                    Loading...
                </p>
            </div>

            <div class="flex gap-x-2">
                <x-filament::icon
                    icon="heroicon-m-clock"
                    class="h-10 w-10 text-gray-400 dark:text-gray-500" />
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>