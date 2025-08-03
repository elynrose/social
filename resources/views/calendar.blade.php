@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Content Calendar</h4>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="calendar.changeView('dayGridMonth')">Month</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="calendar.changeView('timeGridWeek')">Week</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="calendar.changeView('timeGridDay')">Day</button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Post Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="eventDetails"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editEvent">Edit</button>
                <button type="button" class="btn btn-danger" id="deleteEvent">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Add Modal -->
<div class="modal fade" id="quickAddModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Add Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickAddForm">
                    <div class="mb-3">
                        <label for="quickTitle" class="form-label">Title</label>
                        <input type="text" class="form-control" id="quickTitle" required>
                    </div>
                    <div class="mb-3">
                        <label for="quickContent" class="form-label">Content</label>
                        <textarea class="form-control" id="quickContent" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="quickDate" class="form-label">Date & Time</label>
                        <input type="datetime-local" class="form-control" id="quickDate" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Platforms</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="facebook" id="quickFacebook">
                            <label class="form-check-label" for="quickFacebook">Facebook</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="twitter" id="quickTwitter">
                            <label class="form-check-label" for="quickTwitter">Twitter</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="linkedin" id="quickLinkedIn">
                            <label class="form-check-label" for="quickLinkedIn">LinkedIn</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveQuickAdd">Save</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<style>
.fc-event {
    cursor: pointer;
}

.fc-event:hover {
    opacity: 0.8;
}

.event-status-draft {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
}

.event-status-scheduled {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
}

.event-status-published {
    background-color: #198754 !important;
    border-color: #198754 !important;
}

.event-status-failed {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
}

.fc-toolbar-chunk {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.calendar-filters {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-bottom: 1rem;
}

.calendar-filters .form-check {
    margin-bottom: 0;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
let calendar;
let selectedEvent = null;

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        height: 'auto',
        editable: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        weekends: true,
        events: '/api/calendar/events',
        select: function(arg) {
            showQuickAddModal(arg.startStr, arg.endStr);
        },
        eventClick: function(arg) {
            showEventDetails(arg.event);
        },
        eventDrop: function(arg) {
            updateEventDate(arg.event);
        },
        eventResize: function(arg) {
            updateEventDate(arg.event);
        },
        eventDidMount: function(arg) {
            // Add custom styling based on event status
            const status = arg.event.extendedProps.status;
            if (status) {
                arg.el.classList.add(`event-status-${status}`);
            }
        }
    });
    
    calendar.render();
    
    // Load filters
    loadCalendarFilters();
});

function showQuickAddModal(start, end) {
    document.getElementById('quickDate').value = start;
    const modal = new bootstrap.Modal(document.getElementById('quickAddModal'));
    modal.show();
}

function showEventDetails(event) {
    selectedEvent = event;
    
    const details = `
        <div class="row">
            <div class="col-md-8">
                <h6>Content</h6>
                <p>${event.title}</p>
                <div class="mb-3">
                    ${event.extendedProps.content ? `<p><strong>Content:</strong> ${event.extendedProps.content}</p>` : ''}
                    ${event.extendedProps.platforms ? `<p><strong>Platforms:</strong> ${event.extendedProps.platforms.join(', ')}</p>` : ''}
                    <p><strong>Status:</strong> <span class="badge bg-${getStatusColor(event.extendedProps.status)}">${event.extendedProps.status}</span></p>
                    <p><strong>Scheduled for:</strong> ${new Date(event.start).toLocaleString()}</p>
                </div>
            </div>
            <div class="col-md-4">
                <h6>Actions</h6>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editEvent(${event.id})">Edit</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="duplicateEvent(${event.id})">Duplicate</button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteEvent(${event.id})">Delete</button>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('eventDetails').innerHTML = details;
    
    const modal = new bootstrap.Modal(document.getElementById('eventModal'));
    modal.show();
}

function getStatusColor(status) {
    switch (status) {
        case 'draft': return 'secondary';
        case 'scheduled': return 'primary';
        case 'published': return 'success';
        case 'failed': return 'danger';
        default: return 'secondary';
    }
}

function loadCalendarFilters() {
    const filtersContainer = document.createElement('div');
    filtersContainer.className = 'calendar-filters';
    filtersContainer.innerHTML = `
        <strong>Filters:</strong>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="filterDraft" checked>
            <label class="form-check-label" for="filterDraft">Drafts</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="filterScheduled" checked>
            <label class="form-check-label" for="filterScheduled">Scheduled</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="filterPublished" checked>
            <label class="form-check-label" for="filterPublished">Published</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="filterFailed" checked>
            <label class="form-check-label" for="filterFailed">Failed</label>
        </div>
    `;
    
    document.querySelector('.fc-toolbar').appendChild(filtersContainer);
    
    // Add event listeners for filters
    ['filterDraft', 'filterScheduled', 'filterPublished', 'filterFailed'].forEach(id => {
        document.getElementById(id).addEventListener('change', function() {
            applyFilters();
        });
    });
}

function applyFilters() {
    const filters = {
        draft: document.getElementById('filterDraft').checked,
        scheduled: document.getElementById('filterScheduled').checked,
        published: document.getElementById('filterPublished').checked,
        failed: document.getElementById('filterFailed').checked
    };
    
    // Reload events with filters
    calendar.refetchEvents();
}

function updateEventDate(event) {
    const newDate = event.start.toISOString();
    
    fetch(`/api/posts/${event.id}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            scheduled_at: newDate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Event updated successfully', 'success');
        } else {
            showToast('Failed to update event', 'error');
            calendar.refetchEvents(); // Revert changes
        }
    })
    .catch(error => {
        console.error('Error updating event:', error);
        showToast('Failed to update event', 'error');
        calendar.refetchEvents(); // Revert changes
    });
}

function editEvent(eventId) {
    window.location.href = `/posts/${eventId}/edit`;
}

function duplicateEvent(eventId) {
    fetch(`/api/posts/${eventId}/duplicate`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Event duplicated successfully', 'success');
            calendar.refetchEvents();
        } else {
            showToast('Failed to duplicate event', 'error');
        }
    })
    .catch(error => {
        console.error('Error duplicating event:', error);
        showToast('Failed to duplicate event', 'error');
    });
}

function deleteEvent(eventId) {
    if (confirm('Are you sure you want to delete this event?')) {
        fetch(`/api/posts/${eventId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Event deleted successfully', 'success');
                calendar.refetchEvents();
                bootstrap.Modal.getInstance(document.getElementById('eventModal')).hide();
            } else {
                showToast('Failed to delete event', 'error');
            }
        })
        .catch(error => {
            console.error('Error deleting event:', error);
            showToast('Failed to delete event', 'error');
        });
    }
}

// Quick add form submission
document.getElementById('saveQuickAdd').addEventListener('click', function() {
    const form = document.getElementById('quickAddForm');
    const formData = new FormData();
    
    formData.append('title', document.getElementById('quickTitle').value);
    formData.append('content', document.getElementById('quickContent').value);
    formData.append('scheduled_at', document.getElementById('quickDate').value);
    
    const platforms = [];
    if (document.getElementById('quickFacebook').checked) platforms.push('facebook');
    if (document.getElementById('quickTwitter').checked) platforms.push('twitter');
    if (document.getElementById('quickLinkedIn').checked) platforms.push('linkedin');
    formData.append('platforms', JSON.stringify(platforms));
    
    fetch('/api/posts', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Post created successfully', 'success');
            calendar.refetchEvents();
            bootstrap.Modal.getInstance(document.getElementById('quickAddModal')).hide();
            form.reset();
        } else {
            showToast('Failed to create post', 'error');
        }
    })
    .catch(error => {
        console.error('Error creating post:', error);
        showToast('Failed to create post', 'error');
    });
});

function showToast(message, type) {
    // Simple toast implementation - you might want to use a proper toast library
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999;';
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}
</script>
@endpush
@endsection