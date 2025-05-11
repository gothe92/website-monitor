<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold">Kritikus események</h1>
            </div>

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
                        @forelse($criticalEvents as $event)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $event->created_at->format('Y-m-d H:i:s') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $event->website->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @php
                                        $eventType = match(true) {
                                            $event->status === 'error' => 'Hiba',
                                            $event->status === 'success_with_ssl_warning' => 'SSL Figyelmeztetés',
                                            $event->response_time > 10000 => 'Kritikus lassúság',
                                            $event->response_time > 5000 => 'Lassú válasz',
                                            default => 'Normál'
                                        };
                                        
                                        $eventColor = match(true) {
                                            $event->status === 'error' => 'bg-red-100 text-red-800',
                                            $event->status === 'success_with_ssl_warning' => 'bg-yellow-100 text-yellow-800',
                                            $event->response_time > 10000 => 'bg-red-100 text-red-800',
                                            $event->response_time > 5000 => 'bg-yellow-100 text-yellow-800',
                                            default => 'bg-green-100 text-green-800'
                                        };
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $eventColor }}">
                                        {{ $eventType }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($event->status === 'error')
                                        {{ $event->error_message }}
                                    @elseif($event->status === 'success_with_ssl_warning')
                                        {{ $event->error_message }}
                                    @else
                                        Válaszidő: {{ round($event->response_time/1000, 2) }}s
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Nincsenek kritikus események
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $criticalEvents->links() }}
            </div>
        </div>
    </div>
</x-app-layout> 