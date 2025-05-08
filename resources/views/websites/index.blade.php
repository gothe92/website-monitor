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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($websites as $website)
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <h5 class="text-xl font-semibold mb-2">{{ $website->name }}</h5>
                            <p class="text-gray-600 mb-4">{{ $website->url }}</p>
                            <div class="flex justify-between">
                                <a href="{{ route('websites.edit', $website) }}" 
                                   class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700">
                                    Szerkesztés
                                </a>
                                <form action="{{ route('websites.destroy', $website) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-700">
                                        Törlés
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
