<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Workspaces</x-slot>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($this->getWorkspaces() as $workspace)
                <a href="{{ route($workspace['route']) }}" class="group rounded-lg border border-gray-200 p-4 transition hover:border-primary-500 dark:border-gray-700 dark:hover:border-primary-400">
                    <span class="block text-sm font-semibold text-gray-950 dark:text-white">{{ $workspace['title'] }}</span>
                    <span class="mt-1 block text-sm text-gray-500 dark:text-gray-400">{{ $workspace['detail'] }}</span>
                </a>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">No workspaces are assigned to your administrator role.</p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
