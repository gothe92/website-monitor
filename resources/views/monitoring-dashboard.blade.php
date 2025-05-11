<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <h1 class="mb-8 text-3xl font-bold">Website Monitoring Dashboard</h1>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($websites as $website)
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

            {{-- Kritikus események táblázat --}}
            <div class="mt-8">
                <h2 class="text-xl font-semibold mb-4">Kritikus események</h2>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dátum</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weboldal</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Esemény</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Részletek</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php
                                $criticalEvents = collect($websites)->flatMap(function($website) {
                                    return collect($website['logs'])
                                        ->filter(function($log) {
                                            return $log['status'] === 'error' || 
                                                   ($log['response_time'] && $log['response_time'] > 5000);
                                        })
                                        ->map(function($log) use ($website) {
                                            $eventType = match(true) {
                                                $log['status'] === 'error' => 'Hiba',
                                                $log['response_time'] > 10000 => 'Kritikus lassúság',
                                                $log['response_time'] > 5000 => 'Lassú válasz',
                                                default => 'Normál'
                                            };
                                            
                                            $eventColor = match(true) {
                                                $log['status'] === 'error' => 'bg-red-100 text-red-800',
                                                $log['response_time'] > 10000 => 'bg-red-100 text-red-800',
                                                $log['response_time'] > 5000 => 'bg-yellow-100 text-yellow-800',
                                                default => 'bg-green-100 text-green-800'
                                            };

                                            $priority = match(true) {
                                                $log['status'] === 'error' => 1,
                                                $log['response_time'] > 10000 => 2,
                                                $log['response_time'] > 5000 => 3,
                                                default => 4
                                            };

                                            return [
                                                'date' => $log['formatted_time'],
                                                'website' => $website['name'],
                                                'event' => $eventType,
                                                'details' => $log['status'] === 'error' 
                                                    ? 'Nem sikerült elérni a weboldalt' 
                                                    : 'Válaszidő: ' . round($log['response_time']/1000, 2) . 's',
                                                'color' => $eventColor,
                                                'priority' => $priority
                                            ];
                                        });
                                })
                                ->sortBy([
                                    ['priority', 'asc'],
                                    ['date', 'desc']
                                ])
                                ->take(10);
                            @endphp

                            @forelse($criticalEvents as $event)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $event['date'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $event['website'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $event['color'] }}">
                                            {{ $event['event'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $event['details'] }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                        Nincsenek kritikus események az elmúlt időszakból
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
