

@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-3">
        <div class="list-group">
            <a href="#events" class="list-group-item list-group-item-action active" data-bs-toggle="tab">Events</a>
            <a href="#categories" class="list-group-item list-group-item-action" data-bs-toggle="tab">Categories</a>
            <a href="#add-event" class="list-group-item list-group-item-action" data-bs-toggle="tab">Add Event</a>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="tab-content">
      
        
            <div class="tab-pane fade show active" id="events">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Event Management</h4>
                    <select class="form-select w-auto" id="eventFilter">
                        <option value="all">All Events</option>
                        <option value="published">Published</option>
                        <option value="waiting">Waiting for Publish</option>
                    </select>
                </div>
                <div id="eventsList"></div>
            </div>

            
            <div class="tab-pane fade" id="categories">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Category Management</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        Add Category
                    </button>
                </div>
                <div id="categoriesList"></div>
            </div>

            
            <div class="tab-pane fade" id="add-event">
                <h4 class="mb-3">Add New Event</h4>
                <form id="addEventForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-control" id="category" required>
                                    <option value="">Select Category</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" rows="4" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="publish_date" class="form-label">Publish Date & Time</label>
                        <input type="datetime-local" class="form-control" id="publish_date" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="photos" class="form-label">Photos (1-5 images)</label>
                        <input type="file" class="form-control" id="photos" multiple accept="image/*" required>
                        <div class="form-text">Select 1 to 5 photos. Maximum 2MB each.</div>
                    </div>
                    
                    <button type="submit" class="btn btn-success">Add Event</button>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addCategoryForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="categoryName" required>
                    </div>
                    <div class="mb-3">
                        <label for="parentCategory" class="form-label">Parent Category (Optional)</label>
                        <select class="form-control" id="parentCategory">
                            <option value="">No Parent</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this event? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let deleteEventId = null;


    
    document.addEventListener('DOMContentLoaded', function() {
        loadEvents();
        loadCategories();
        loadCategoryOptions();
        

        
        const user = JSON.parse(localStorage.getItem('user'));
        if (user) {
            document.getElementById('userInfo').textContent = `Welcome, ${user.username}`;
        }
    });

   
    
    document.getElementById('eventFilter').addEventListener('change', loadEvents);


    
    async function loadEvents() {
        const filter = document.getElementById('eventFilter').value;
        try {
            const response = await axios.get(`/api/admin/events?filter=${filter}`);
            displayEvents(response.data.events);
        } catch (error) {
            console.error('Error loading events:', error);
        }
    }

    
function displayEvents(events) {
    const container = document.getElementById('eventsList');
    
    if (events.length === 0) {
        container.innerHTML = '<div class="alert alert-info">No events found.</div>';
        return;
    }

    container.innerHTML = events.map(event => {
        
        
        // const isPublished = event.is_published !== undefined ? 
        //     event.is_published : 
        //     new Date(event.publish_date) <= new Date();

        const publishTime = event.publish_date_display;
            const isPublished = event.is_published;

            
        const statusBadge = isPublished ? 
            '<span class="badge bg-success">Published</span>' : 
            '<span class="badge bg-warning">Waiting</span>';
            
        // const publishTime = event.publish_date_display || 
        //     new Date(event.publish_date).toLocaleString();

        return `
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>${event.title}</h5>
                            <p class="text-muted">${event.description}</p>
                            <div class="d-flex gap-3 text-sm align-items-center">
                                <span><strong>Category:</strong> ${event.category.name}</span>
                                <span><strong>Publish:</strong> ${publishTime}</span>
                                ${statusBadge}
                                ${!isPublished ? `<span class="text-muted">(${timeUntilPublish(event.publish_date)})</span>` : ''}
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <button class="btn btn-danger btn-sm" onclick="confirmDelete(${event.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                    ${event.photos.length > 0 ? `
                        <div class="mt-3">
                            <strong>Photos:</strong>
                            <div class="d-flex gap-2 mt-2">
                                ${event.photos.map(photo => `
                                    <img src="/storage/${photo.photo_path}" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                `).join('')}
                            </div>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }).join('');
}


function timeUntilPublish(publishDate) {
    const now = new Date();
    const publish = new Date(publishDate);
    
    console.log('Current UTC:', now.toISOString());
    console.log('Publish UTC:', publish.toISOString());
    
    const diffMs = publish - now;
    console.log('Time difference:', diffMs, 'ms');
    
    if (diffMs <= 0) {
        console.log('Event should be published!');
        return 'Now';
    }
    
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
    const diffHours = Math.floor((diffMs % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
    
    let result = '';
    
    if (diffDays > 0) {
        result = `in ${diffDays}d ${diffHours}h`;
    } else if (diffHours > 0) {
        result = `in ${diffHours}h ${diffMinutes}m`;
    } else if (diffMinutes > 0) {
        result = `in ${diffMinutes}m`;
    } else {
        result = 'in less than a minute';
    }
    
    
    return result;
}

   
    function confirmDelete(eventId) {
        deleteEventId = eventId;
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }


    document.getElementById('confirmDelete').addEventListener('click', async function() {
        if (!deleteEventId) return;
        
        try {
            await axios.delete(`/api/events/${deleteEventId}`);
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            loadEvents();
            deleteEventId = null;
        } catch (error) {
            console.error('Error deleting event:', error);
            alert('Error deleting event');
        }
    });


    async function loadCategories() {
        try {
           
            const response = await axios.get('/api/categories/nested');
            // console.log('Categories loaded:', response.data);
            displayCategories(response.data.categories);
        } catch (error) {
            console.error('Error loading categories:', error);
            console.error('Error response:', error.response);
            
            const container = document.getElementById('categoriesList');
            container.innerHTML = `
                <div class="alert alert-danger">
                    Failed to load categories: ${error.response?.data?.message || error.message}
                </div>
            `;
        }
    }

    async function loadCategoryOptions() {
        try {
           
            const response = await axios.get('/api/categories/nested');
            const categories = response.data.categories;
            
            
            // For event form
            const eventCategorySelect = document.getElementById('category');
            eventCategorySelect.innerHTML = '<option value="">Select Category</option>' + 
                generateCategoryOptions(categories);
            
            // For category form
            const parentCategorySelect = document.getElementById('parentCategory');
            parentCategorySelect.innerHTML = '<option value="">No Parent</option>' + 
                generateCategoryOptions(categories);
                
        } catch (error) {
            console.error('Error loading category options:', error);
            console.error('Error response:', error.response);
            
            // Set default options even if loading fails
            const eventCategorySelect = document.getElementById('category');
            eventCategorySelect.innerHTML = '<option value="">Failed to load categories</option>';
            
            const parentCategorySelect = document.getElementById('parentCategory');
            parentCategorySelect.innerHTML = '<option value="">Failed to load categories</option>';
        }
    }

    
    function generateCategoryOptions(categories, level = 0) {
        let options = '';
        const prefix = '─'.repeat(level);
        
        categories.forEach(category => {
            options += `<option value="${category.id}">${prefix} ${category.name}</option>`;
            if (category.children && category.children.length > 0) {
                options += generateCategoryOptions(category.children, level + 1);
            }
        });
        
        return options;
    }


    
    function displayCategories(categories) {
        const container = document.getElementById('categoriesList');
        
        if (categories.length === 0) {
            container.innerHTML = '<div class="alert alert-info">No categories found.</div>';
            return;
        }

        container.innerHTML = '<ul class="list-group">' + 
            generateCategoryList(categories) + 
        '</ul>';
    }

    
    function generateCategoryList(categories, level = 0) {
        let html = '';
        const padding = level * 20;
        
        categories.forEach(category => {
            html += `
                <li class="list-group-item" style="padding-left: ${padding + 20}px">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>${category.name}</span>
                        <small class="text-muted">ID: ${category.id}</small>
                    </div>
                </li>
            `;
            
            if (category.children && category.children.length > 0) {
                html += generateCategoryList(category.children, level + 1);
            }
        });
        
        return html;
    }

    
document.getElementById('addEventForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const title = document.getElementById('title').value;
    const description = document.getElementById('description').value;
    const categoryId = document.getElementById('category').value;
    const publishDateInput = document.getElementById('publish_date').value;
    
    console.log('=== FORM SUBMISSION DEBUG ===');
    console.log('Raw datetime-local value:', publishDateInput);
    
    // Create date object from the input (this will be in local timezone)
    const localDate = new Date(publishDateInput);
    console.log('As local Date:', localDate.toString());
    console.log('As ISO string:', localDate.toISOString());
    
    const formData = new FormData();
    formData.append('title', title);
    formData.append('description', description);
    formData.append('category_id', categoryId);
    formData.append('publish_date', localDate.toISOString()); // Send as ISO string
    
    const photos = document.getElementById('photos').files;
    for (let i = 0; i < photos.length; i++) {
        formData.append('photos[]', photos[i]);
    }

    try {
        const response = await axios.post('/api/events', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        alert('Event added successfully!');
        this.reset();
        loadEvents();
    } catch (error) {
        console.error('Error adding event:', error);
        alert('Error adding event: ' + (error.response?.data?.message || 'Unknown error'));
    }
});



    document.getElementById('addCategoryForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const data = {
            name: document.getElementById('categoryName').value,
            parent_id: document.getElementById('parentCategory').value || null
        };

        try {
          
            
            const response = await axios.post('/api/categories', data);
        
            
            
            alert('Category added successfully!');
            this.reset();
            bootstrap.Modal.getInstance(document.getElementById('addCategoryModal')).hide();
            
            // Reload both categories and options
            await loadCategories();
            await loadCategoryOptions();
            
        } catch (error) {
            console.error('Error adding category:', error);
            console.error('Error response:', error.response);
            
            const errorMessage = error.response?.data?.message || 
                                error.response?.data?.error || 
                                'Error adding category';
            alert('Error: ' + errorMessage);
        }
    });
</script>
@endsection