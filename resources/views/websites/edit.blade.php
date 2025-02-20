<x-app-layout>
    <div class="container px-4 py-8 mx-auto">
        <div class="max-w-md mx-auto  ">
            <div class="px-6 py-4 bg-white shadow-lg   rounded-lg">
                <h2 class="mb-4 text-2xl font-bold">Weboldal Szerkesztése</h2>

                <form action="{{ route('websites.update', $website) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="name" class="block mb-2 text-sm font-bold text-gray-700">Név</label>
                        <input type="text" name="name" id="name"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror"
                            value="{{ old('name', $website->name) }}" required>
                        @error('name')
                        <p class="mt-1 text-xs italic text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="url" class="block mb-2 text-sm font-bold text-gray-700">URL</label>
                        <input type="url" name="url" id="url"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('url') border-red-500 @enderror"
                            value="{{ old('url', $website->url) }}" required>
                        @error('url')
                        <p class="mt-1 text-xs italic text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-bold text-gray-700">Monitorozási Statisztikák</label>
                        <div class="p-3 text-sm rounded bg-gray-50">
                            <p class="mb-1">Utolsó ellenőrzés:
                                <span class="font-medium">
                                    {{ $website->latestLog ? $website->latestLog->created_at->diffForHumans() : 'Még nem volt ellenőrizve' }}
                                </span>
                            </p>
                            <p class="mb-1">Átlagos válaszidő:
                                <span class="font-medium">
                                    {{ $website->average_response_time ? round($website->average_response_time / 1000, 2) . 's' : 'N/A' }}
                                </span>
                            </p>
                            <p>Státusz:
                                <span
                                    class="inline-flex px-2 text-xs leading-5 font-semibold rounded-full
                                {{ $website->latestLog?->status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $website->latestLog?->status === 'success' ? 'Online' : 'Offline' }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <button type="submit"
                            class="px-4 py-2 font-bold text-white bg-blue-500 rounded hover:bg-blue-700 focus:outline-none focus:shadow-outline">
                            Mentés
                        </button>

                    </div>
                </form>
            </div>
            <div class="flex space-x-4 mt-4 justify-end">
                <form action="{{ route('websites.destroy', $website) }}" method="POST" class="inline"
                    onsubmit="return confirm('Biztosan törölni szeretnéd ezt a weboldalt?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-4 py-2 font-bold text-white bg-red-500 rounded hover:bg-red-700 focus:outline-none focus:shadow-outline">
                        Törlés
                    </button>
                </form>

            </div>
        </div>

    </div>
</x-app-layout>