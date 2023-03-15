<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            訂單列表
        </h2>
    </x-slot>

    <article>
        @foreach ($orders as $order)
        <div class="first-of-type:pt-12 py-1">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <p class="">訂單編號： {{ $order->id }} 建立日期： {{ $order->created_at }}</p>
                    </div>
                </div>
            </div>
        </div>

        @endforeach
        @if (!$orders->count())
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <p>尚無資料</p>
                    </div>
                </div>
            </div>
        </div>

        @endif
    </article>

</x-app-layout>
