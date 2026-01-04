@extends('layouts.admin')

@section('title', 'System Updates')

@section('content')
<div class="w-full px-0 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">System Updates</h1>
        <p class="text-gray-600 mt-2">Manage and apply system updates for {{ $systemName ?? config('app.name', 'BattleMaster') }}</p>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
            <p class="font-bold">Success!</p>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
            <p class="font-bold">Error!</p>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <!-- Current Version Info -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 w-full">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <p class="text-blue-100 text-sm mb-1">Current Version</p>
            <h2 class="text-3xl font-bold">{{ $currentVersion }}</h2>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <p class="text-purple-100 text-sm mb-1">Last Check</p>
            <h2 class="text-xl font-bold">
                @if($lastCheck)
                    {{ $lastCheck->diffForHumans() }}
                @else
                    Never
                @endif
            </h2>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <p class="text-green-100 text-sm mb-1">Status</p>
            <h2 class="text-xl font-bold">
                @if($updateAvailable)
                    <span class="text-xs text-gray-400">No updates available</span>
                @endif
            </h2>
        </div>
    </div>

    {{-- Updates Table --}}
    @if(isset($updates) && count($updates) > 0)
    <div class="w-full mb-8">
        <div class="bg-white rounded-lg shadow-lg overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Version</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Release Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($updates as $update)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap font-bold">{{ $update['version'] ?? $update->version }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $update['release_date'] ?? $update->release_date ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $update['description'] ?? $update->description ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if(empty($update['local_status']))
                                <button onclick="startUpdate('{{ $update['version'] ?? $update->version }}')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Update</button>
                            @elseif(!empty($update['can_rollback']))
                                <button onclick="rollback('{{ $update['version'] ?? $update->version }}')" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Rollback</button>
                            @else
                                <span class="text-gray-400 text-xs">Up to date</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="text-center py-8 text-gray-500">
        <svg class="mx-auto mb-4 w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3 class="text-xl font-bold mb-2">No updates available</h3>
        <p class="text-gray-400">There are currently no updates available for {{ $systemName ?? config('app.name', 'BattleMaster') }}.</p>
    </div>
    @endif

<!-- Update Progress Modal -->
<div id="updateModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Installing Update</h3>
            <div id="updateContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<script>
let updateCheckInProgress = false;
let updateInProgress = false;
let progressInterval = null;

// Function to refresh the page with cache busting
function refreshPage() {
    // Clear any intervals
    if (progressInterval) clearInterval(progressInterval);
    
    // Force reload with cache busting
    window.location.href = window.location.pathname + '?updated=' + Date.now();
}

function checkForUpdates(elementOrEvent) {
    if (updateCheckInProgress) return;

    updateCheckInProgress = true;
    // Resolve button element from argument (could be `this` from onclick or an Event)
    let button = null;
    if (!elementOrEvent) {
        button = document.activeElement;
    } else if (elementOrEvent instanceof Event) {
        button = elementOrEvent.currentTarget || elementOrEvent.target;
    } else if (elementOrEvent instanceof Element) {
        button = elementOrEvent;
    } else {
        button = document.activeElement;
    }

    const originalText = button && button.innerHTML ? button.innerHTML : '';
    if (button) {
        button.disabled = true;
        button.innerHTML = '<svg class="animate-spin h-5 w-5 inline mr-2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Checking...';
    }

    fetch('{{ route('admin.system.update.check') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to check for updates: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to check for updates. Please try again.');
    })
    .finally(() => {
        updateCheckInProgress = false;
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

function startUpdate(version) {
    if (updateInProgress) return;
    
    if (!confirm('This will update ' + (window.systemName || '{{ $systemName ?? config('app.name', 'BattleMaster') }}') + ' to version ' + version + '.\n\nThe site will be put in maintenance mode for regular users during the update, but you (as admin) will still have full access to the admin panel.\n\nContinue?')) {
        return;
    }
    
    updateInProgress = true;
    showUpdateModal();
    
    // Show admin notice about maintenance mode
    const adminNotice = document.createElement('div');
    adminNotice.className = 'bg-blue-50 border-l-4 border-blue-500 p-4 mb-4 text-sm';
    adminNotice.innerHTML = `
        <p class="font-bold text-blue-800">ℹ️ Admin Access Maintained</p>
        <p class="text-blue-700">The site is now in maintenance mode for users, but admin routes remain accessible.</p>
    `;
    document.getElementById('updateContent').insertBefore(adminNotice, document.getElementById('updateContent').firstChild);
    
    // Start polling for progress immediately
    progressInterval = setInterval(() => {
        updateProgress(version);
    }, 2000);
    
    fetch('{{ route('admin.system.update.apply', ['version' => "__VERSION__"]) }}'.replace('__VERSION__', version), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ version: version })
    })
    .then(response => {
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // If not JSON, the update might have completed but response was cut off
            // Check the update status via progress endpoint
            return fetch('{{ route('admin.system.update.progress') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ version: version })
            })
            .then(r => r.json())
            .then(progressData => {
                if (progressData.success && progressData.status === 'completed') {
                    return { success: true, version: version, message: 'Update completed successfully' };
                } else {
                    return { success: false, message: 'Update response was invalid' };
                }
            });
        }
    })
    .then(data => {
        if (data.success) {
            document.getElementById('updateContent').innerHTML = `
                <div class="text-center py-8">
                    <div class="flex justify-center mb-4">
                        <svg class="w-16 h-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Update Successful!</h3>
                    <p class="text-gray-600 mb-4">{{ $systemName ?? config('app.name', 'BattleMaster') }} has been updated to version ${data.version || version}</p>
                    <p class="text-gray-500 text-sm mb-4">Page will refresh automatically in <span id="countdown">3</span> seconds...</p>
                    <button onclick="refreshPage()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Refresh Now
                    </button>
                </div>
            `;
            
            // Auto-refresh after 3 seconds
            let countdown = 3;
            const countdownEl = document.getElementById('countdown');
            const timer = setInterval(() => {
                countdown--;
                if (countdownEl) countdownEl.textContent = countdown;
                if (countdown <= 0) {
                    clearInterval(timer);
                    refreshPage();
                }
            }, 1000);
        } else {
            document.getElementById('updateContent').innerHTML = `
                <div class="text-center py-8">
                    <div class="flex justify-center mb-4">
                        <svg class="w-16 h-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Update Failed</h3>
                    <p class="text-gray-600 mb-4">${data.message || 'An unknown error occurred'}</p>
                    <button onclick="closeUpdateModal()" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Close
                    </button>
                </div>
            `;
        }
        updateInProgress = false;
        if (progressInterval) {
            clearInterval(progressInterval);
        }
    })
    .catch(error => {
        console.error('Update request error:', error);
        
        // Don't immediately show error - check the progress endpoint first
        // The update might have completed successfully despite the connection error
        fetch('{{ route('admin.system.update.progress') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ version: version })
        })
        .then(response => response.json())
        .then(progressData => {
            if (progressData.success && progressData.status === 'completed' && progressData.progress === 100) {
                // Update actually completed successfully!
                document.getElementById('updateContent').innerHTML = `
                    <div class="text-center py-8">
                        <div class="flex justify-center mb-4">
                            <svg class="w-16 h-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Update Successful!</h3>
                        <p class="text-gray-600 mb-4">{{ $systemName ?? config('app.name', 'BattleMaster') }} has been updated to version ${version}</p>
                        <p class="text-gray-500 text-sm mb-4">Page will refresh automatically in <span id="countdown">3</span> seconds...</p>
                        <button onclick="refreshPage()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Refresh Now
                        </button>
                    </div>
                `;
                
                // Auto-refresh after 3 seconds
                let countdown = 3;
                const countdownEl = document.getElementById('countdown');
                const timer = setInterval(() => {
                    countdown--;
                    if (countdownEl) countdownEl.textContent = countdown;
                    if (countdown <= 0) {
                        clearInterval(timer);
                        refreshPage();
                    }
                }, 1000);
            } else {
                // Actually failed
                document.getElementById('updateContent').innerHTML = `
                    <div class="text-center py-8">
                        <div class="flex justify-center mb-4">
                            <svg class="w-16 h-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">Update Failed</h3>
                        <p class="text-gray-600 mb-4">${progressData.error || 'An error occurred during the update process'}</p>
                        <button onclick="closeUpdateModal()" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Close
                        </button>
                    </div>
                `;
            }
        })
        .catch(progressError => {
            // Can't even check progress - show generic error
            console.error('Progress check error:', progressError);
            document.getElementById('updateContent').innerHTML = `
                <div class="text-center py-8">
                    <div class="flex justify-center mb-4">
                        <svg class="w-16 h-16 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Connection Error</h3>
                    <p class="text-gray-600 mb-4">Lost connection to the server. The update may have completed successfully.</p>
                    <p class="text-sm text-gray-500 mb-4">Please refresh the page to check the current version.</p>
                    <button onclick="refreshPage()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                        Refresh Page
                    </button>
                    <button onclick="closeUpdateModal()" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Close
                    </button>
                </div>
            `;
        })
        .finally(() => {
            updateInProgress = false;
            if (progressInterval) {
                clearInterval(progressInterval);
            }
        });
    });
}

function showUpdateModal() {
    document.getElementById('updateModal').classList.remove('hidden');
    document.getElementById('updateContent').innerHTML = `
        <div class="text-center py-8">
            <div class="flex justify-center mb-4">
                <svg class="animate-spin h-12 w-12 text-blue-600" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Preparing Update...</h3>
            <p class="text-gray-600">Please wait while we update your system.</p>
            <div class="mt-4">
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div id="progressBar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <p id="progressText" class="text-sm text-gray-600 mt-2">Initializing...</p>
            </div>
        </div>
    `;
}

function closeUpdateModal() {
    document.getElementById('updateModal').classList.add('hidden');
    updateInProgress = false;
    if (progressInterval) {
        clearInterval(progressInterval);
    }
}

function updateProgress(version) {
    fetch('{{ route('admin.system.update.progress') }}?version=' + version)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const progressBar = document.getElementById('progressBar');
            const progressText = document.getElementById('progressText');
            
            if (progressBar) {
                progressBar.style.width = data.progress + '%';
            }
            
            if (progressText) {
                const statusText = {
                    'downloading': 'Downloading update package...',
                    'extracting': 'Extracting files...',
                    'backing_up': 'Creating backup...',
                    'migrating': 'Running database migrations...',
                    'completed': 'Update completed!'
                };
                progressText.textContent = statusText[data.status] || 'Processing...';
            }
        }
    });
}

function rollback(version) {
    if (!confirm('Are you sure you want to rollback to the previous version? This will restore all files from the backup.')) {
        return;
    }
    
    fetch('{{ route('admin.system.update.rollback', ['version' => "__VERSION__"]) }}'.replace('__VERSION__', version), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ version: version })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Rollback completed successfully!');
            location.reload();
        } else {
            alert('Rollback failed: ' + data.message);
        }
    });
}
</script>
@endsection
