<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="eyebrow">Registration</p>
            <h1 class="page-title">New beneficiary profile</h1>
        </div>
    </x-slot>

    <section class="page-shell">
        <form class="panel-card" method="POST" action="{{ route('beneficiaries.store') }}" enctype="multipart/form-data">
            @include('beneficiaries._form')
        </form>
    </section>
</x-app-layout>
