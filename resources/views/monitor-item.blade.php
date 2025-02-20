<x-app-layout>
    <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
    <h1 class="mb-8 text-3xl font-bold">Website Monitoring Dashboard</h1>

    <div class="grid grid-cols-1 gap-6">
        @foreach($websites as $website)
        <div class="flex items-center p-4 bg-white rounded-lg shadow-lg justify-between">
            <div class="flex-1">
                <h2 class="text-xl font-semibold">{{ $website['name'] }}</h2>
                <a href="{{ $website['url'] }}" class="text-sm text-blue-600 hover:text-blue-800" target="_blank">
                    {{ $website['url'] }}
                </a>
            </div>

            {{-- Státusz jelző --}}
            <div class="flex items-center">
                <span class="px-3 py-1 rounded-full text-sm {{ $website['stats']['status']['is_online'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $website['stats']['status']['is_online'] ? 'Online' : 'Offline' }}
                </span>
            </div>

            {{-- Válaszidő grafikon (kicsiben) --}}
            <div class="relative flex flex-col h-20 rounded-lg bg-gray-50 flex-1 ml-4">
                <div class="flex w-full h-full">
                    @foreach($website['logs'] as $log)
                        @php
                            $height = $log['response_time'] ? ($log['response_time'] / 10000) * 100 : 0;
                            $color = match(true) {
                                !$log['response_time'] => 'bg-gray-200',
                                $log['status'] === 'error' => 'bg-red-500',
                                $log['response_time'] > 10000 => 'bg-yellow-500',
                                $log['response_time'] > 5000 => 'bg-orange-400',
                                default => 'bg-green-500'
                            };
                        @endphp
                        <div class="flex items-end flex-1 mx-px" title="{{ $log['formatted_time'] }} - {{ $log['response_time'] ? round($log['response_time']/1000, 2).'s' : 'No data' }}">
                            <div class="{{ $color }} hover:opacity-75 transition-opacity flex-1"
                                 style="height: {{ max($height, 5) }}%">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>
    </div>
</div>

<script>
    // Automatikus frissítés 60 másodpercenként
    setInterval(() => {
        window.location.reload();
    }, 60000);
</script>
</x-app-layout>

