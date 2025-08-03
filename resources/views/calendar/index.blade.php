@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar"></i> Content Calendar
                    </h5>
                    <div class="btn-group">
                        <a href="{{ route('calendar.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Schedule Post
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshCalendar()">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Calendar View -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Calendar View</h6>
                                </div>
                                <div class="card-body">
                                    <div id="calendar"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scheduled Posts List -->
                    <div class="row">
                        <div class="col-md-12">
                            <h6>Scheduled Posts</h6>
                            @if($scheduledPosts->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Post</th>
                                                <th>Scheduled For</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($scheduledPosts as $scheduledPost)
                                                <tr>
                                                    <td>
                                                        <div>
                                                            <strong>{{ Str::limit($scheduledPost->post->content, 50) }}</strong>
                                                            @if($scheduledPost->post->campaign)
                                                                <br><small class="text-muted">Campaign: {{ $scheduledPost->post->campaign->name }}</small>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong>{{ $scheduledPost->publish_at->format('M j, Y') }}</strong>
                                                            <br><small class="text-muted">{{ $scheduledPost->publish_at->format('g:i A') }}</small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @switch($scheduledPost->status)
                                                            @case('scheduled')
                                                                <span class="badge bg-primary">Scheduled</span>
                                                                @break
                                                            @case('published')
                                                                <span class="badge bg-success">Published</span>
                                                                @break
                                                            @case('failed')
                                                                <span class="badge bg-danger">Failed</span>
                                                                @break
                                                            @case('cancelled')
                                                                <span class="badge bg-secondary">Cancelled</span>
                                                                @break
                                                            @default
                                                                <span class="badge bg-secondary">{{ ucfirst($scheduledPost->status) }}</span>
                                                        @endswitch
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="{{ route('calendar.edit', $scheduledPost) }}" 
                                                               class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form action="{{ route('calendar.destroy', $scheduledPost) }}" 
                                                                  method="POST" 
                                                                  class="d-inline"
                                                                  onsubmit="return confirm('Are you sure you want to cancel this scheduled post?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination -->
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $scheduledPosts->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Scheduled Posts</h5>
                                    <p class="text-muted">Schedule your first post to see it here.</p>
                                    <a href="{{ route('calendar.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Schedule Your First Post
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css' rel='stylesheet' />
<style>
/* FullCalendar Custom Styles */
.fc {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 20px;
}

.fc-toolbar {
    background: #f8f9fa !important;
    padding: 15px !important;
    border-radius: 8px !important;
    margin-bottom: 20px !important;
    border: 1px solid #e9ecef !important;
}

.fc-toolbar-title {
    font-size: 1.5rem !important;
    font-weight: 600 !important;
    color: #495057 !important;
}

.fc-button {
    background-color: #007bff !important;
    border-color: #007bff !important;
    color: white !important;
    font-weight: 500 !important;
    padding: 8px 16px !important;
    border-radius: 6px !important;
    transition: all 0.2s ease !important;
}

.fc-button:hover {
    background-color: #0056b3 !important;
    border-color: #0056b3 !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3) !important;
}

.fc-button:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    outline: none !important;
}

.fc-button-active {
    background-color: #0056b3 !important;
    border-color: #0056b3 !important;
}

.fc-daygrid-day {
    min-height: 100px !important;
    border: 1px solid #e9ecef !important;
}

.fc-daygrid-day-number {
    font-weight: 600 !important;
    color: #495057 !important;
    padding: 8px !important;
}

.fc-day-today {
    background-color: #f8f9fa !important;
}

.fc-event {
    cursor: pointer !important;
    border-radius: 4px !important;
    padding: 4px 8px !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    border: none !important;
    margin: 1px !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    transition: all 0.2s ease !important;
}

.fc-event:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
}

.fc-event-title {
    font-weight: 600 !important;
}

.fc-daygrid-event-dot {
    border-width: 4px !important;
}

.fc-daygrid-day-events {
    margin-top: 4px !important;
}

.fc-more-link {
    color: #007bff !important;
    font-weight: 500 !important;
    text-decoration: none !important;
}

.fc-more-link:hover {
    color: #0056b3 !important;
    text-decoration: underline !important;
}

.fc-popover {
    border-radius: 8px !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    border: 1px solid #e9ecef !important;
}

.fc-popover-header {
    background-color: #f8f9fa !important;
    border-bottom: 1px solid #e9ecef !important;
    padding: 12px 16px !important;
    font-weight: 600 !important;
}

.fc-popover-body {
    padding: 12px 16px !important;
}

/* Event colors based on status */
.fc-event-scheduled {
    background-color: #007bff !important;
    color: white !important;
}

.fc-event-published {
    background-color: #28a745 !important;
    color: white !important;
}

.fc-event-failed {
    background-color: #dc3545 !important;
    color: white !important;
}

.fc-event-cancelled {
    background-color: #6c757d !important;
    color: white !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .fc-toolbar {
        flex-direction: column !important;
        gap: 10px !important;
    }
    
    .fc-toolbar-chunk {
        display: flex !important;
        justify-content: center !important;
    }
    
    .fc-daygrid-day {
        min-height: 80px !important;
    }
}

#calendar {
    min-height: 600px;
}
</style>
@endpush

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing calendar...');
    var calendarEl = document.getElementById('calendar');
    console.log('Calendar element:', calendarEl);
    
    if (calendarEl) {
        console.log('Creating FullCalendar...');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: {
                url: '{{ route("calendar.events") }}',
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            },
            eventClick: function(info) {
                console.log('Event clicked:', info.event.title);
                console.log('Event URL:', info.event.url);
                if (info.event.url) {
                    console.log('Navigating to:', info.event.url);
                    window.location.href = info.event.url;
                    return false;
                } else {
                    console.log('No URL found for event');
                }
            },
            dateClick: function(info) {
                console.log('Date clicked:', info.dateStr);
                // Navigate to create post page with the selected date
                var createUrl = '{{ route("posts.create") }}?scheduled_date=' + info.dateStr;
                window.location.href = createUrl;
            },
            eventDidMount: function(info) {
                console.log('Event mounted:', info.event.title);
                // Add tooltips if jQuery is available
                if (typeof $ !== 'undefined') {
                    $(info.el).tooltip({
                        title: info.event.title,
                        placement: 'top',
                        trigger: 'hover',
                        container: 'body'
                    });
                }
                
                // Add custom classes based on event status
                var status = info.event.extendedProps.status || 'scheduled';
                info.el.classList.add('fc-event-' + status);
            },
            eventDisplay: 'block',
            dayMaxEvents: 3,
            moreLinkClick: 'popover',
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short'
            },
            dayCellDidMount: function(arg) {
                // Add hover effect to day cells
                arg.el.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f8f9fa';
                });
                arg.el.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                });
            },
            loading: function(isLoading) {
                console.log('Calendar loading:', isLoading);
            },
            eventSourceFailure: function(error) {
                console.error('Calendar event source failed:', error);
            }
        });
        console.log('Rendering calendar...');
        calendar.render();
        console.log('Calendar rendered successfully');
    } else {
        console.error('Calendar element not found!');
    }
});

function refreshCalendar() {
    location.reload();
}
</script>
@endpush
@endsection 