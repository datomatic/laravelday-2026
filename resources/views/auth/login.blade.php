<x-layouts.app>
    <div class="mx-auto mt-12 max-w-sm">
        <div class="mb-6 text-center">
            <span class="mx-auto grid size-12 place-items-center rounded-2xl bg-gradient-to-br from-red-500 to-red-700 text-xl font-bold text-white shadow-lg shadow-red-600/30">L</span>
            <h1 class="mt-4 text-2xl font-bold tracking-tight">Bentornato</h1>
            <p class="text-sm text-zinc-500">Accedi per costruire la tua agenda.</p>
        </div>

        <form method="POST" action="{{ route('login.store') }}" class="card space-y-4 p-6">
            @csrf

            <label class="block text-sm">
                <span class="font-medium text-zinc-700">Email</span>
                <input type="email" name="email" value="{{ old('email', 'test@example.com') }}" required autofocus
                       class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100">
            </label>

            <label class="block text-sm">
                <span class="font-medium text-zinc-700">Password</span>
                <input type="password" name="password" required
                       class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 outline-none transition focus:border-red-400 focus:ring-4 focus:ring-red-100">
            </label>

            <button class="btn-primary w-full justify-center py-2.5">Entra</button>
        </form>
    </div>
</x-layouts.app>
