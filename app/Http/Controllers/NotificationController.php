<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\User;
use App\Services\NotificationBroadcaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', [
            'notifications' => $notifications,
            'moduleOptions' => User::moduleCatalog(),
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $user->notifications()
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? 'Notification',
                'message' => $notification->data['message'] ?? '',
                'kind' => $notification->data['kind'] ?? 'announcement',
                'audience' => $notification->data['audience_label'] ?? null,
                'is_read' => $notification->read_at !== null,
                'created_at' => $notification->created_at?->diffForHumans(),
                'open_url' => route('notifications.open', $notification->id),
            ]);

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    public function open(Request $request, string $notification): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($notification);
        $notification->markAsRead();
        $data = $notification->data;
        $module = $data['action_module'] ?? null;

        if ($module && !$request->user()->hasModuleAccess($module)) {
            return redirect()->route('notifications.index')
                ->with('error', 'Your current account access does not include that module.');
        }

        $routeName = $data['action_route'] ?? 'notifications.index';
        $allowedRoutes = ['dashboard', 'documents.show', 'notifications.index'];

        if (!in_array($routeName, $allowedRoutes, true) || !Route::has($routeName)) {
            return redirect()->route('notifications.index');
        }

        $parameters = is_array($data['action_parameters'] ?? null)
            ? $data['action_parameters']
            : [];

        if ($routeName === 'documents.show') {
            $documentId = filter_var($parameters['document'] ?? null, FILTER_VALIDATE_INT);

            if (!$documentId || !Document::whereKey($documentId)->exists()) {
                return redirect()->route('notifications.index')
                    ->with('error', 'The referenced document is no longer available.');
            }

            $parameters = ['document' => $documentId];
        } else {
            $parameters = [];
        }

        return redirect()->route($routeName, $parameters);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }

    public function broadcast(Request $request, NotificationBroadcaster $broadcaster): RedirectResponse
    {
        $allowedModules = array_keys(User::moduleCatalog());
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'message' => ['required', 'string', 'min:3', 'max:1000'],
            'audience' => ['required', Rule::in(['all', 'modules'])],
            'modules' => ['nullable', 'array'],
            'modules.*' => ['string', Rule::in($allowedModules)],
        ]);

        $modules = array_values(array_unique($validated['modules'] ?? []));

        if ($validated['audience'] === 'modules' && !$modules) {
            return back()
                ->withErrors(['modules' => 'Select at least one department module.'])
                ->withInput();
        }

        $sent = $validated['audience'] === 'all'
            ? $broadcaster->toAll($validated['title'], $validated['message'])
            : $broadcaster->toModules($modules, $validated['title'], $validated['message']);

        return redirect()->route('notifications.index')
            ->with('success', "Announcement sent to {$sent} user(s).");
    }
}
