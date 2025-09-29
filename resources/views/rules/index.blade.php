@extends('layout')

@section('content')
    <div class="container py-8">
        <h1 class="text-xl font-medium mb-6">Rules</h1>
        <div class="flex justify-between mb-4">
            <a href="{{ route('rules.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">Create Rule</a>
        </div>
        <div class="tabs mb-4">
            <a href="?tab=main" class="{{ $tab === 'main' ? 'active' : '' }}">Main</a>
            <a href="?tab=archived" class="{{ $tab === 'archived' ? 'active' : '' }}">Archived</a>
        </div>
        <!-- Filters form -->
        <form method="GET" class="grid grid-cols-4 gap-4 mb-6">
            <!-- Search by name, status, apply_to, discount_type, min/max value, start/end from/to -->
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name">
            <select name="status">
                <option value="">Status</option>
                <option value="on" {{ ($filters['status'] ?? '') === 'on' ? 'selected' : '' }}>On</option>
                <option value="off" {{ ($filters['status'] ?? '') === 'off' ? 'selected' : '' }}>Off</option>
            </select>
            <!-- Tương tự cho apply_to, discount_type, etc. -->
            <!-- ... -->
            <button type="submit">Apply Filters</button>
        </form>
        <table class="table">
            <thead>
                <tr>
                    <th><a href="?sort_field=name&sort_dir={{ $sortDir === 'asc' ? 'desc' : 'asc' }}">Name</a></th>
                    <th>Conditions</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rules as $rule)
                    <tr>
                        <td>{{ $rule->name }}</td>
                        <td>
                            Discount: {{ $rule->discount_value }} {{ $rule->discount_type === 'percentage' ? '%' : 'USD' }}<br>
                            Apply to: {{ $rule->apply_to }}<br>
                            Start: {{ $rule->start_time }}<br>
                            End: {{ $rule->end_time ?? 'N/A' }}<br>
                            Exclude: {{ count(json_decode($rule->excluded_ids, true) ?? []) }}
                        </td>
                        <td>
                            @if ($rule->status === 'on')
                                @if ($rule->start_time <= now() && ($rule->end_time === null || $rule->end_time > now()))
                                    Finishes in {{ now()->diffForHumans($rule->end_time) }}
                                @elseif ($rule->start_time > now())
                                    Will active in future
                                @else
                                    Disabled in past
                                @endif
                            @else
                                Off
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('rules.toggleStatus', $rule) }}" method="POST">
                                @csrf
                                <button type="submit">{{ $rule->status === 'on' ? 'Turn Off' : 'Turn On' }}</button>
                            </form>
                            @if ($tab === 'main')
                                <a href="{{ route('rules.duplicate', $rule) }}">Duplicate</a>
                                <a href="{{ route('rules.edit', $rule) }}">Edit</a>
                                <form action="{{ route('rules.archive', $rule) }}" method="POST">
                                    @csrf
                                    <button type="submit">Archive</button>
                                </form>
                            @else
                                <form action="{{ route('rules.restore', $rule) }}" method="POST">
                                    @csrf
                                    <button type="submit">Restore</button>
                                </form>
                                <form action="{{ route('rules.destroy', $rule) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $rules->links() }}
    </div>
@endsection