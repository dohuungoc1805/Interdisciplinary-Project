@extends('layouts.admin')
@section('title', 'Người dùng')
@section('content')
    <div class="admin-shell">
        <div class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Người dùng</h1>
                <p class="admin-page-lead">Tìm theo tên hoặc email, chặn / bỏ chặn tài khoản.</p>
            </div>
        </div>

        <form method="get" class="admin-card mb-6 max-w-md">
            <label class="admin-label" for="q">Tìm kiếm</label>
            <div class="flex gap-2">
                <input id="q" name="q" class="admin-input flex-1" value="{{ request('q') }}" placeholder="Tên hoặc email…" />
                <button class="admin-btn-outline shrink-0" type="submit">Lọc</button>
            </div>
        </form>

        <div class="admin-table-wrap overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Chặn</th>
                        <th class="text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                        <tr>
                            <td class="font-medium text-slate-900">{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->is_blocked ? 'Có' : 'Không' }}</td>
                            <td class="text-right">
                                <form method="post" action="{{ route('admin.users.toggle-block', $u) }}" class="inline">@csrf
                                    <button class="admin-link text-sm" type="submit">{{ $u->is_blocked ? 'Bỏ chặn' : 'Chặn' }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $users->links() }}</div>
    </div>
@endsection
