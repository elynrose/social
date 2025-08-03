@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Notifications</h4>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="markAllAsRead()">
                            <i class="fas fa-check-double"></i> Mark All as Read
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshNotifications()">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($notifications->count() > 0)
                        <div class="list-group">
                            @foreach($notifications as $notification)
                                <div class="list-group-item list-group-item-action {{ $notification->isRead() ? '' : 'list-group-item-primary' }}" 
                                     id="notification-{{ $notification->id }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="mb-0">{{ $notification->title }}</h6>
                                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                            </div>
                                            <p class="mb-2">{{ $notification->message }}</p>
                                            
                                            @if($notification->data)
                                                <div class="small text-muted">
                                                    @if(isset($notification->data['type']))
                                                        <span class="badge bg-secondary">{{ $notification->data['type'] }}</span>
                                                    @endif
                                                    @if(isset($notification->data['action_url']))
                                                        <a href="{{ $notification->data['action_url'] }}" class="btn btn-sm btn-outline-primary">
                                                            View Details
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <div class="btn-group-vertical ms-3">
                                            @if(!$notification->isRead())
                                                <button type="button" class="btn btn-sm btn-outline-success" onclick="markAsRead({{ $notification->id }})">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteNotification({{ $notification->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No notifications</h5>
                            <p class="text-muted">You're all caught up! New notifications will appear here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function markAsRead(notificationId) {
    fetch(`/api/notifications/${notificationId}/read`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notification = document.getElementById(`notification-${notificationId}`);
            notification.classList.remove('list-group-item-primary');
            updateUnreadCount();
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function markAllAsRead() {
    fetch('/api/notifications/mark-all-read', {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove primary class from all notifications
            document.querySelectorAll('.list-group-item-primary').forEach(item => {
                item.classList.remove('list-group-item-primary');
            });
            updateUnreadCount();
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
}

function deleteNotification(notificationId) {
    if (confirm('Are you sure you want to delete this notification?')) {
        fetch(`/api/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notification = document.getElementById(`notification-${notificationId}`);
                notification.remove();
                updateUnreadCount();
            }
        })
        .catch(error => {
            console.error('Error deleting notification:', error);
        });
    }
}

function refreshNotifications() {
    location.reload();
}

function updateUnreadCount() {
    fetch('/api/notifications/unread-count')
        .then(response => response.json())
        .then(data => {
            // Update notification badge in navbar if it exists
            const badge = document.querySelector('.notification-badge');
            if (badge) {
                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count;
                    badge.style.display = 'inline';
                } else {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error updating unread count:', error);
        });
}

// Auto-refresh notifications every 30 seconds
setInterval(() => {
    updateUnreadCount();
}, 30000);

// Initialize unread count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateUnreadCount();
});
</script>
@endpush
@endsection 