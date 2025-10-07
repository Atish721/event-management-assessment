<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $userTimezone = $request->user()->timezone ?? 'UTC';
        
        try {
            
            $query = "
                select e.*, c.name as category_name, u.username as user_username
                from events e
                left join categories c on e.category_id = c.id
                left join users u on e.user_id = u.id
                where e.publish_date <= ?
            ";

            $params = [now()];

            
            if ($request->category_id) {
                $query .= " AND e.category_id = ?";
                $params[] = $request->category_id;
            }

            $query .= " order by e.publish_date asc";

            
            $events = DB::select($query, $params);

            
            $formattedEvents = [];
            foreach ($events as $event) {
                $photos = DB::select('select id, photo_path from event_photos where event_id = ?', [$event->id]);
                
                
                // $publishDate = Carbon::parse($event->publish_date);
                $publishDate = Carbon::parse($event->publish_date, 'UTC')->setTimezone($userTimezone);

                $publishDateFormatted = $publishDate->setTimezone($userTimezone)->format('Y-m-d H:i:s');
                $publishDateDisplay = $publishDate->setTimezone($userTimezone)->format('M j, Y g:i A');
                
                $formattedEvents[] = [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'publish_date' => $event->publish_date,
                    'publish_date_formatted' => $publishDateFormatted,
                    'publish_date_display' => $publishDateDisplay,
                    'is_published' => true, 
                    
                    'category' => [
                        'id' => $event->category_id,
                        'name' => $event->category_name
                    ],
                    'user' => [
                        'id' => $event->user_id,
                        'username' => $event->user_username
                    ],
                    'photos' => $photos
                ];
            }

            return response()->json(['events' => $formattedEvents]);

        } catch (\Exception $e) {
          
            
            return response()->json(['error' => 'Failed to fetch events'], 500);
        }
    }

public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'category_id' => 'required|exists:categories,id',
        'publish_date' => 'required|date|after:now',
        'photos' => 'required|array|min:1|max:5',
        'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    try {
           
    
        
        // $publishDateUTC = Carbon::parse($request->publish_date)->setTimezone('UTC');
         $userTimezone = $request->user()->timezone ?? 'UTC';
        $publishDateUTC = Carbon::parse($request->publish_date, $userTimezone)->setTimezone('UTC');
        
      
        
        if ($publishDateUTC->isPast()) {
            throw ValidationException::withMessages([
                'publish_date' => 'The publish date must be in the future. Please select a future date and time.',
            ]);
        }

        
        $categoryExists = DB::selectOne('SELECT id FROM categories WHERE id = ?', [$request->category_id]);
        if (!$categoryExists) {
            throw ValidationException::withMessages([
                'category_id' => ['The selected category does not exist.'],
            ]);
        }

        
        DB::beginTransaction();

        
        DB::insert('
            insert into events (title, description, category_id, user_id, publish_date, created_at, updated_at) 
            values (?, ?, ?, ?, ?, ?, ?)
        ', [
            $request->title,
            $request->description,
            $request->category_id,
            $request->user()->id,
            $publishDateUTC->format('Y-m-d H:i:s'),
            now(),
            now()
        ]);

        $eventId = DB::getPdo()->lastInsertId();

        
        $uploadedPhotos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('event-photos', 'public');
                
                DB::insert('
                    insert into event_photos (event_id, photo_path, created_at, updated_at) 
                    values (?, ?, ?, ?)
                ', [
                    $eventId,
                    $path,
                    now(),
                    now()
                ]);

                $photoId = DB::getPdo()->lastInsertId();
                $uploadedPhotos[] = [
                    'id' => $photoId,
                    'photo_path' => $path
                ];
            }
        }

     
        
        DB::commit();

        return response()->json([
            'message' => 'Event created successfully',
            'event_id' => $eventId
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
      
        return response()->json(['error' => 'Failed to create event: ' . $e->getMessage()], 500);
    }
}

    public function destroy(Request $request, $id)
    {
        try {
      
            
            DB::beginTransaction();

   
            
            $event = DB::selectOne('
                select id, user_id 
                from events 
                where id = ? and user_id = ?
            ', [$id, $request->user()->id]);

            if (!$event) {
                return response()->json(['message' => 'Event not found or access denied'], 404);
            }
            
            $photos = DB::select('select id, photo_path from event_photos where event_id = ?', [$id]);
  
            
            foreach ($photos as $photo) {
                Storage::disk('public')->delete($photo->photo_path);
            }

            
            DB::delete('delete from event_photos where event_id = ?', [$id]);

            
            DB::delete('delete from events where id = ?', [$id]);

            
            DB::commit();

            return response()->json(['message' => 'Event deleted successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
         
            return response()->json(['error' => 'Failed to delete event'], 500);
        }
    }

  public function adminList(Request $request)
{
    $filter = $request->get('filter', 'all');
    $userTimezone = $request->user()->timezone ?? 'UTC';
    
    try {
        
        $query = "
            select e.*, c.name as category_name, u.username as user_username
            from events e
            left join categories c on e.category_id = c.id
            left join users u on e.user_id = u.id
            where 1=1
        ";

        $params = [];

        
        if ($filter === 'published') {
            $query .= " and e.publish_date <= ?";
            $params[] = now()->format('Y-m-d H:i:s');
        } elseif ($filter === 'waiting') {
            $query .= " and e.publish_date > ?";
            $params[] = now()->format('Y-m-d H:i:s');
        }

        $query .= " order by e.created_at DESC";

        
        $events = DB::select($query, $params);

        
        $formattedEvents = [];
        foreach ($events as $event) {
            $photos = DB::select('select id, photo_path from event_photos where event_id = ?', [$event->id]);
            
            
            // $publishDate = Carbon::parse($event->publish_date);
            $publishDate = Carbon::parse($event->publish_date, 'UTC')->setTimezone($userTimezone);

            $publishDateFormatted = $publishDate->setTimezone($userTimezone)->format('Y-m-d H:i:s');
            $publishDateDisplay = $publishDate->setTimezone($userTimezone)->format('M j, Y g:i A');
            
            
            $isPublished = $this->isEventPublished($event->publish_date);
            
            $formattedEvents[] = [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'publish_date' => $event->publish_date,
                'publish_date_formatted' => $publishDateFormatted,
                'publish_date_display' => $publishDateDisplay,
                'is_published' => $isPublished,
                'category' => [
                    'id' => $event->category_id,
                    'name' => $event->category_name
                ],
                'user' => [
                    'id' => $event->user_id,
                    'username' => $event->user_username
                ],
                'photos' => $photos
            ];
        }

        return response()->json(['events' => $formattedEvents]);

    } catch (\Exception $e) {
      
        return response()->json(['error' => 'Failed to fetch events'], 500);
    }
}



private function isEventPublished($publishDate)
{
    // return Carbon::parse($publishDate)->isPast();
    return Carbon::parse($publishDate, 'UTC')->isPast();
}

 

    public function show($id)
    {
        try {
            $event = DB::selectOne('
                select e.*, c.name as category_name, u.username as user_username
                from events e
                left join categories c on e.category_id = c.id
                left join users u on e.user_id = u.id
                WHERE e.id = ?
            ', [$id]);

            if (!$event) {
                return response()->json(['message' => 'Event not found'], 404);
            }

            $photos = DB::select('select id, photo_path from event_photos where event_id = ?', [$id]);

            $formattedEvent = [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'publish_date' => $event->publish_date,
                'category' => [
                    'id' => $event->category_id,
                    'name' => $event->category_name
                ],
                'user' => [
                    'id' => $event->user_id,
                    'username' => $event->user_username
                ],
                'photos' => $photos
            ];

            return response()->json(['event' => $formattedEvent]);

        } catch (\Exception $e) {
          
            return response()->json(['error' => 'Failed to fetch event'], 500);
        }
    }


    
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'publish_date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            
            $event = DB::selectOne('
                select id, user_id 
                from events 
                where id = ? and user_id = ?
            ', [$id, $request->user()->id]);

            if (!$event) {
                return response()->json(['message' => 'Event not found or access denied'], 404);
            }

            
            $userTimezone = $request->user()->timezone ?? 'UTC';
            $publishDateUTC = Carbon::parse($request->publish_date, $userTimezone)
                ->setTimezone('UTC');

                
            DB::update('
                update events 
                set title = ?, description = ?, category_id = ?, publish_date = ?, updated_at = ?
                where id = ?
            ', [
                $request->title,
                $request->description,
                $request->category_id,
                $publishDateUTC,
                now(),
                $id
            ]);

            return response()->json([
                'message' => 'Event updated successfully'
            ]);

        } catch (\Exception $e) {
           
            return response()->json(['error' => 'Failed to update event'], 500);
        }
    }
}