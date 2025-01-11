<x-app-layout>
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold">Monitorozott Weboldalak</h1>
        <a href="{{ route('websites.create') }}"
           class="px-4 py-2 font-bold text-white bg-blue-500 rounded hover:bg-blue-700">
            Új Weboldal
        </a>
    </div>

    <div class="overflow-hidden bg-white rounded-lg shadow">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Név</th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">URL</th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Státusz</th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Műveletek</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($websites as $website)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $website->name }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ $website->url }}" class="text-blue-600 hover:text-blue-900" target="_blank">
                            {{ $website->url }}
                        </a>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            {{ $website->latestLog?->status === 'success'
                                ? 'bg-green-100 text-green-800'
                                : 'bg-red-100 text-red-800' }}">
                            {{ $website->latestLog?->status === 'success' ? 'Online' : 'Offline' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                        <a href="{{ route('websites.edit', $website) }}"
                           class="mr-3 text-indigo-600 hover:text-indigo-900">Szerkesztés</a>

                        <form action="{{ route('websites.destroy', $website) }}"
                              method="POST"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="text-red-600 hover:text-red-900"
                                    onclick="return confirm('Biztosan törölni szeretnéd?')">
                                Törlés
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</div>
</x-app-layout>
