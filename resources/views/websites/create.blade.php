<x-app-layout>
<div class="container px-4 py-8 mx-auto">
    <div class="max-w-md mx-auto overflow-hidden bg-white rounded-lg shadow-lg">
        <div class="px-6 py-4">
            <h2 class="mb-4 text-2xl font-bold">Új Weboldal Hozzáadása</h2>

            <form action="{{ route('websites.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="name" class="block mb-2 text-sm font-bold text-gray-700">Név</label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="w-full px-3 py-2 leading-tight text-gray-700 border rounded shadow appearance-none focus:outline-none focus:shadow-outline"
                           value="{{ old('name') }}"
                           required>
                </div>

                <div class="mb-4">
                    <label for="url" class="block mb-2 text-sm font-bold text-gray-700">URL</label>
                    <input type="url"
                           name="url"
                           id="url"
                           class="w-full px-3 py-2 leading-tight text-gray-700 border rounded shadow appearance-none focus:outline-none focus:shadow-outline"
                           value="{{ old('url') }}"
                           required>
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit"
                            class="px-4 py-2 font-bold text-white bg-blue-500 rounded hover:bg-blue-700 focus:outline-none focus:shadow-outline">
                        Hozzáadás
                    </button>
                    <a href="{{ route('websites.index') }}"
                       class="text-gray-600 hover:text-gray-800">
                        Mégse
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

</x-app-layout>
