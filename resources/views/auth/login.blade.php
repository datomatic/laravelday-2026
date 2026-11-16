<x-layouts.app>
    <div class="mx-auto mt-12 max-w-sm">
        <div class="mb-6 text-center">
            <img src="{{ asset('images/laravelday-icon.png') }}" alt="" class="mx-auto size-12 rounded-2xl">
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
