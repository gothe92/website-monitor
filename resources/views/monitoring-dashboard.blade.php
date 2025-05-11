<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

        
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @php
                    $sortedWebsites = collect($websites)->map(function($website) {
                        $website['average_response_time'] = collect($website['logs'])->avg('response_time');
                        return $website;
                    })->sortByDesc('average_response_time');
                @endphp
                
                @foreach ($sortedWebsites as $website)
                    <div class="flex flex-col p-4 bg-white rounded-lg shadow-lg">
                        {{-- 1. Cím és URL --}}

                        <div class="mb-4">
                            <a href="{{ $website['url'] }}" target="_blank">
                                <h2 class="text-lg font-semibold">{{ $website['name'] }}</h2>
                            </a>
                        </div>

                        <div class="grid grid-cols-2 mb-4">


                            @php
                                $averageResponseTime = collect($website['logs'])->avg('response_time');
                            @endphp

                            <div>
                                <span class="text-sm text-gray-600">
                                    Átlag:
                                    {{ $averageResponseTime ? round($averageResponseTime / 1000, 2) . 's' : 'Nincs adat' }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span
                                    class="px-3 py-1 rounded-full text-sm mt-2 {{ $website['stats']['status']['is_online'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $website['stats']['status']['is_online'] ? 'Online' : 'Offline' }}
                                </span>
                            </div>


                        </div>

                        {{-- 3. Válaszidő grafikon (utolsó óra) --}}
                        <div class="relative flex flex-col h-16 rounded-lg bg-gray-50 mt-auto">
                            <div class="flex w-full h-full">
                                @php
                                    $lastHourLogs = collect($website['logs'])->take(-120);
                                @endphp
                                @foreach ($lastHourLogs as $log)
                                    @php
                                        $height = $log['response_time'] ? ($log['response_time'] / 10000) * 100 : 0;
                                        $color = match (true) {
                                            !$log['response_time'] => 'bg-gray-200',
                                            $log['status'] === 'error' => 'bg-red-500',
                                            $log['response_time'] > 10000 => 'bg-yellow-500',
                                            $log['response_time'] > 5000 => 'bg-orange-400',
                                            default => 'bg-green-500',
                                        };
                                    @endphp
                                    <div class="flex items-end flex-1 mr-[1px]"
                                        title="{{ $log['formatted_time'] }} - {{ $log['response_time'] ? round($log['response_time'] / 1000, 2) . 's' : 'No data' }}">
                                        <div class="{{ $color }} hover:opacity-75 transition-opacity w-full"
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
