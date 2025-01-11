<x-app-layout>
<div class="container px-4 py-8 mx-auto">
    <h1 class="mb-8 text-3xl font-bold">Website Monitoring Dashboard</h1>

    <div class="grid grid-cols-1 gap-6">
        @foreach($websites as $website)
        <div class="p-6 bg-white rounded-lg shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <div>
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
            </div>

            {{-- Válaszidő grafikon --}}
            <div class="relative flex flex-col h-40 rounded-lg bg-gray-50">
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

                {{-- Időskála --}}
                <div class="flex justify-between px-2 py-1 text-xs text-gray-600 bg-white bg-opacity-75 ">
                    <span>-60m</span>
                    <span>-45m</span>
                    <span>-30m</span>
                    <span>-15m</span>
                    <span>Now</span>
                </div>
            </div>

            {{-- Statisztikák --}}
            <div class="grid grid-cols-4 gap-4 mt-4 text-sm">
                <div class="p-3 rounded bg-gray-50">
                    <div class="text-gray-600">Átlag válaszidő</div>
                    <div class="font-semibold">
                        {{ round($website['stats']['average_response']/1000, 2) }}s
                    </div>
                </div>
                <div class="p-3 rounded bg-gray-50">
                    <div class="text-gray-600">Min válaszidő</div>
                    <div class="font-semibold">
                        {{ round($website['stats']['min_response']/1000, 2) }}s
                    </div>
                </div>
                <div class="p-3 rounded bg-gray-50">
                    <div class="text-gray-600">Max válaszidő</div>
                    <div class="font-semibold">
                        {{ round($website['stats']['max_response']/1000, 2) }}s
                    </div>
                </div>
                <div class="p-3 rounded bg-gray-50">
                    <div class="text-gray-600">Rendelkezésre állás</div>
                    <div class="font-semibold">
                        {{ $website['stats']['availability'] }}%
                    </div>
                </div>
            </div>

            {{-- Teljesítmény mutatók --}}
            <div class="flex gap-4 mt-4 text-sm">
                <div class="flex items-center">
                    <span class="inline-block w-3 h-3 rounded-full {{ match($website['performance_indicators']['average_response_trend']) {
                        'increasing' => 'bg-red-500',
                        'decreasing' => 'bg-green-500',
                        default => 'bg-gray-500'
                    } }} mr-2"></span>
                    <span class="text-gray-600">Trend: {{ $website['performance_indicators']['average_response_trend'] }}</span>
                </div>
                <div>
                    <span class="text-yellow-600">{{ $website['performance_indicators']['slow_responses'] }} lassú válasz</span>
                </div>
                <div>
                    <span class="text-red-600">{{ $website['performance_indicators']['errors'] }} hiba</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
    // Automatikus frissítés 60 másodpercenként
    setInterval(() => {
        window.location.reload();
    }, 60000);
</script>
</x-app-layout>

