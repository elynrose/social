@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Posts</h1>
        <a href="/posts/create" class="btn btn-primary">New Post</a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Content</th>
                        <th>Status</th>
                        <th>Scheduled At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Lorem ipsum dolor sit amet…</td>
                        <td><span class="badge bg-secondary">Draft</span></td>
                        <td>—</td>
                        <td>
                            <a href="#" class="btn btn-sm btn-outline-primary disabled">Edit</a>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Sed do eiusmod tempor incididunt…</td>
                        <td><span class="badge bg-success">Scheduled</span></td>
                        <td>2025-08-10 14:00 UTC</td>
                        <td>
                            <a href="#" class="btn btn-sm btn-outline-primary disabled">Edit</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection