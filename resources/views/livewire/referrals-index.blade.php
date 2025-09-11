<div>
    <div class="referral-container">
        <h2>Referrals Management</h2>
        <p class="subtitle">Monitor patient referrals with real-time updates.</p>

        <!-- Filter & Search Bar -->
        <div class="filter-search-bar">
            <label for="statusFilter">Status:</label>
            <select id="statusFilter" wire:model="statusFilter">
                <option value="all">All</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>

            <input
                id="search"
                type="text"
                wire:model.debounce.500ms="search"
                placeholder="Search by Referral ID, Patient..."
            />
        </div>

        <!-- Referrals Table -->
        <table class="nice-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Patient Name</th>
                    <th>Facility</th> <!-- NEW COLUMN -->
                    <th>Status</th>
                    <th>Progress</th>
                </tr>
            </thead>
            <tbody>
                @forelse($referrals as $referral)
                    @php
                        $statusClass = match($referral->status) {
                            'in_progress' => 'status-inprogress',
                            'completed'   => 'status-completed',
                            default       => 'status-default',
                        };

                        // Calculate progress percentage
                        $totalSteps = $referral->workflow->steps->count();
                        $completedSteps = $referral->progress->where('status', 'completed')->count();
                        $progressPercent = $totalSteps 
                            ? ($completedSteps / $totalSteps) * 100 
                            : 0;

                        // Retrieve info from first step if available
                        $patientName = '—';
                        $facilityName = '—';

                        $firstStep = $referral->progress->first();
                        if($firstStep && $firstStep->notes) {
                            $notesData = json_decode($firstStep->notes, true);

                            // Build patient name
                            $patientName = ($notesData['first_name'] ?? '') 
                                           . ' ' 
                                           . ($notesData['last_name'] ?? '');
                            $patientName = trim($patientName) ?: '—';

                            // Facility (if exists in notes)
                            $facilityName = $notesData['facility'] 
                                            ?? '—';
                        }
                    @endphp
                    <tr>
                        <td>#{{ $referral->id }}</td>
                        <td>{{ $patientName }}</td>
                        <td>{{ $facilityName }}</td> <!-- NEW CELL -->
                        <td>
                            <span class="status-badge {{ $statusClass }}">
                                {{ ucfirst(str_replace('_', ' ', $referral->status)) }}
                            </span>
                        </td>
                        <td>
                            <div class="progress-container">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: {{ $progressPercent }}%;"></div>
                                </div>
                                <small class="progress-text">
                                    {{ $completedSteps }}/{{ $totalSteps }} steps completed
                                </small>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="no-data">No referrals found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($referrals->hasPages())
            <div class="pagination">
                {{ $referrals->links() }}
            </div>
        @endif
    </div>

    <style>
    .referral-container {
      max-width: 900px;
      margin: 0 auto;
      padding: 20px;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #333;
    }

    .referral-container h2 {
      font-size: 26px;
      margin-bottom: 5px;
    }

    .subtitle {
      font-size: 14px;
      color: #666;
      margin-bottom: 20px;
    }

    .filter-search-bar {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
    }

    .filter-search-bar select,
    .filter-search-bar input {
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 4px;
      outline: none;
    }

    .nice-table {
      width: 100%;
      border-collapse: collapse;
    }

    .nice-table th,
    .nice-table td {
      padding: 10px;
      border: 1px solid #e2e8f0;
      text-align: left;
    }

    .nice-table th {
      background-color: #f5f7fa;
    }

    .nice-table tr:hover {
      background-color: #fafcff;
    }

    .progress-container {
      width: 100%;
      background-color: #e4eaf1;
      border-radius: 8px;
      overflow: hidden;
    }

    .progress-bar {
      width: 100%;
      background-color: #e4ebf2;
      height: 8px;
      border-radius: 8px;
      overflow: hidden;
    }

    .progress-fill {
      background-color: #4f93e6;
      height: 100%;
      transition: width 0.3s ease;
    }

    .no-data {
      padding: 15px;
      text-align: center;
      color: #999;
      font-style: italic;
    }

    .pagination .links a,
    .pagination .links span {
      padding: 4px 8px;
      border-radius: 4px;
      border: 1px solid #ddd;
      color: #4f93e6;
      margin-right: 4px;
    }

    .pagination .links span {
      color: #999;
    }

    .status-inprogress {
      background-color: #e0f0ff;
      color: #007bff;
      padding: 4px 8px;
      border-radius: 4px;
    }

    .status-completed {
      background-color: #d4f4dc;
      color: #0f9b5a;
      padding: 4px 8px;
      border-radius: 4px;
    }

    .status-default {
      background-color: #f0f0f0;
      color: #777;
      padding: 4px 8px;
      border-radius: 4px;
    }
    </style>
</div>
