<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="eyebrow">Record Management</p>
            <h1 class="page-title">Update beneficiary profile</h1>
        </div>
    </x-slot>

    <section class="page-shell">
        <form class="panel-card" method="POST" action="{{ route('beneficiaries.update', $beneficiary) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('beneficiaries._form')
        </form>
    </section>
</x-app-layout>
