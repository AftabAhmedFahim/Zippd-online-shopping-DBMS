@extends('admin.layout')

@section('admin-content')
<section class="space-y-2">
    <h1 class="font-mono text-[42px] leading-[0.95] tracking-[-0.02em] text-black">Users Directory</h1>
    <p class="font-roboto text-[15px] text-black/70">
        Live data from the database. This page is read-only for now.
    </p>
</section>

@if (!empty($deletionUpdates))
    <section class="dashboard-solid-card rounded-2xl p-5">
        <div class="mb-3 flex items-center gap-2">
            <span class="admin-status-badge admin-status-danger">Recent Updates</span>
            <h2 class="font-mono text-xl leading-none">Account Deletion Notifications</h2>
        </div>
        <div class="space-y-2">
            @foreach ($deletionUpdates as $update)
                <article class="admin-list-card admin-list-card-rose">
                    <div>
                        <p class="font-roboto text-sm font-semibold text-black">{{ $update['title'] ?? 'User account deleted' }}</p>
                        <p class="font-roboto text-sm text-black/75">{{ $update['message'] ?? 'A user account was deleted.' }}</p>
                    </div>
                    <p class="font-roboto text-xs text-black/55">
                        {{ !empty($update['event_at']) ? \Carbon\Carbon::parse($update['event_at'])->format('M d, Y h:i A') : 'Unknown time' }}
                    </p>
                </article>
            @endforeach
        </div>
    </section>
@endif

<section class="dashboard-solid-card overflow-hidden rounded-2xl">
    <header class="border-b border-black/10 px-5 py-4">
        <label class="admin-search-wrap max-w-md">
            <svg class="h-4 w-4 text-black/40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 110-15 7.5 7.5 0 010 15z" />
            </svg>
            <input
                id="admin-users-live-search"
                type="text"
                value="{{ $initialSearchQuery ?? '' }}"
                class="admin-search-input"
                placeholder="Search by name, email, phone, address, or date..."
                autocomplete="off"
            />
        </label>
    </header>

    <div class="overflow-x-auto">
        <table class="admin-table min-w-[980px]">
            <thead>
            <tr>
                <th>User ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Gender</th>
                <th>Address</th>
                <th>Created At</th>
                <th>Updated At</th>
            </tr>
            </thead>
            <tbody id="admin-users-table-body">
            @forelse ($users as $user)
                @php
                    $createdAt = !empty($user['created_at']) ? \Carbon\Carbon::parse($user['created_at'])->format('Y-m-d') : '-';
                    $updatedAt = !empty($user['updated_at']) ? \Carbon\Carbon::parse($user['updated_at'])->format('Y-m-d') : '-';
                    $searchableText = strtolower(trim(implode(' ', [
                        (string) ($user['user_id'] ?? ''),
                        (string) ($user['full_name'] ?? ''),
                        (string) ($user['email'] ?? ''),
                        (string) ($user['phone'] ?? ''),
                        (string) ($user['gender'] ?? ''),
                        (string) ($user['address'] ?? ''),
                        $createdAt,
                        $updatedAt,
                    ])));
                @endphp
                <tr data-search-text="{{ e($searchableText) }}">
                    <td class="font-semibold text-black">{{ $user['user_id'] ?? '-' }}</td>
                    <td>{{ $user['full_name'] ?? '-' }}</td>
                    <td>{{ $user['email'] ?? '-' }}</td>
                    <td>{{ $user['phone'] ?? '-' }}</td>
                    <td>{{ $user['gender'] ?? '-' }}</td>
                    <td class="max-w-md">{{ $user['address'] ?? '-' }}</td>
                    <td>{{ $createdAt }}</td>
                    <td>{{ $updatedAt }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="py-8 text-center text-black/55">
                        No users found for this search.
                    </td>
                </tr>
            @endforelse
            <tr id="admin-users-no-match-row" style="display: none;">
                <td colspan="8" class="py-8 text-center text-black/55">
                    No users found for this search.
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('admin-users-live-search');
    const tableBody = document.getElementById('admin-users-table-body');
    const noMatchRow = document.getElementById('admin-users-no-match-row');

    if (!input || !tableBody || !noMatchRow) {
        return;
    }

    const dataRows = Array.from(tableBody.querySelectorAll('tr')).filter((row) => {
        return row.id !== 'admin-users-no-match-row' && row.hasAttribute('data-search-text');
    });

    const applyFilter = () => {
        const query = input.value.trim().toLowerCase();
        let visibleCount = 0;

        dataRows.forEach((row) => {
            const haystack = row.getAttribute('data-search-text') || '';
            const isMatch = query === '' || haystack.includes(query);

            row.style.display = isMatch ? '' : 'none';
            if (isMatch) {
                visibleCount += 1;
            }
        });

        noMatchRow.style.display = visibleCount === 0 ? '' : 'none';
    };

    input.addEventListener('input', applyFilter);
    applyFilter();
});
</script>
@endsection
