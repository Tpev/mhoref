<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-3xl font-bold text-gray-900">
                Patient Discharge Dashboard
            </h2>
            <span class="text-sm italic text-gray-500">
                Welcome, Jane Doe (Nurse, Discharge Coordinator)
            </span>
        </div>
    </x-slot>

    <div class="py-8 px-4 max-w-7xl mx-auto space-y-6">

        <!-- Metrics Card -->
        <section class="bg-gradient-to-r from-green-400 to-blue-500 text-white p-6 rounded-xl shadow-md hover:shadow-xl transition transform hover:-translate-y-1">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div>
                    <div class="text-4xl font-extrabold">15</div>
                    <div class="uppercase text-xs tracking-wide">Referrals in Progress</div>
                </div>
                <div>
                    <div class="text-4xl font-extrabold">4</div>
                    <div class="uppercase text-xs tracking-wide">Completed Today</div>
                </div>
                <div>
                    <div class="text-4xl font-extrabold">5</div>
                    <div class="uppercase text-xs tracking-wide">Discharges Next 24h</div>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8 space-y-6">

                <!-- Tasks Card -->
                <div class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition">
                    <h3 class="text-xl font-semibold border-b pb-3 mb-4">My Tasks</h3>
                    <ul class="space-y-3">
                        <li class="flex justify-between items-center bg-gray-50 p-3 rounded hover:bg-green-50 transition">
                            <div>
                                <div class="font-medium">Collect Discharge Papers</div>
                                <div class="text-sm text-gray-500">John Appleseed | Due: Today</div>
                            </div>
                            <a href="#" class="text-green-600 hover:underline">Go to Step</a>
                        </li>
                        <li class="flex justify-between items-center bg-gray-50 p-3 rounded hover:bg-green-50 transition">
                            <div>
                                <div class="font-medium">Confirm DME Setup</div>
                                <div class="text-sm text-gray-500">Alice Brown | Due: Tomorrow</div>
                            </div>
                            <a href="#" class="text-green-600 hover:underline">Go to Step</a>
                        </li>
                    </ul>
                </div>

                <!-- Upcoming Discharges -->
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <h3 class="p-5 text-xl font-semibold border-b">Upcoming Discharges</h3>
                    <table class="min-w-full">
                        <thead class="bg-green-100 text-left text-xs uppercase tracking-wide">
                            <tr>
                                <th class="px-4 py-2">Patient</th>
                                <th class="px-4 py-2">Date</th>
                                <th class="px-4 py-2">Status</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr class="hover:bg-green-50 transition">
                                <td class="px-4 py-2">Thomas Green</td>
                                <td class="px-4 py-2">03/14/2025</td>
                                <td class="px-4 py-2 text-yellow-700">In Progress</td>
                                <td class="px-4 py-2">
                                    <a href="#" class="text-green-600 hover:underline">View</a>
                                </td>
                            </tr>
                            <tr class="hover:bg-green-50 transition">
                                <td class="px-4 py-2">Helen White</td>
                                <td class="px-4 py-2">03/15/2025</td>
                                <td class="px-4 py-2 text-yellow-700">In Progress</td>
                                <td class="px-4 py-2">
                                    <a href="#" class="text-green-600 hover:underline">View</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Chart Card -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-xl font-semibold mb-4">Referrals Trend</h3>
                    <canvas id="referralsChart"></canvas>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Alerts Card -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-xl font-semibold mb-3">Alerts & Notifications</h3>
                    <ul class="space-y-4">
                        <li class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
                            <strong>Sedation Docs Required</strong>
                            <p class="text-sm text-gray-600">Patient: Mark Redwood | 3 hours ago</p>
                            <button class="text-blue-500 hover:underline">Dismiss</button>
                        </li>
                        <li class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
                            <strong>New Referral from Dr. Smith</strong>
                            <p class="text-sm text-gray-600">Patient: Casey Johnson | Today, 8:00 AM</p>
                            <button class="text-blue-500 hover:underline">Dismiss</button>
                        </li>
                    </ul>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-xl font-semibold mb-3">Quick Actions</h3>
                    <div class="space-y-2">
                        <a href="#" class="block bg-green-500 text-white rounded py-2 px-4 text-center hover:bg-green-600 transition">New Referral</a>
                        <a href="#" class="block bg-gray-100 rounded py-2 px-4 text-center hover:bg-gray-200 transition">Patient Search</a>
                        <a href="#" class="block bg-gray-100 rounded py-2 px-4 text-center hover:bg-gray-200 transition">My Incomplete Steps</a>
                        <a href="#" class="block bg-gray-100 rounded py-2 px-4 text-center hover:bg-gray-200 transition">View All Referrals</a>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Chart.js Setup -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('referralsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                datasets: [{
                    label: 'Referrals',
                    data: [12, 8, 14, 19, 10],
                    backgroundColor: 'rgba(22,163,74,0.2)',
                    borderColor: 'rgba(22,163,74,1)',
                    tension: 0.3
                }]
            },
            options: { responsive: true }
        });
    </script>
</div>


