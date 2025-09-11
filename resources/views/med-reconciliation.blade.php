<!-- resources/views/med-reconciliation.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-800">Medication Reconciliation</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Facility Medication List -->
            <div>
                <h3 class="text-xl font-semibold mb-3">Facility Medication List</h3>
                <textarea id="facilityMedList"
                          rows="15"
                          class="w-full rounded-lg border border-gray-300 shadow-sm p-4"
                          placeholder="Paste Facility medication list here...">Aspirin 81 mg daily
Metformin 500 mg twice daily
Lisinopril 10 mg daily
Atorvastatin 20 mg daily
Albuterol inhaler as needed
Omeprazole 20 mg daily
Gabapentin 300 mg nightly</textarea>
            </div>

            <!-- Epic Medication List -->
            <div>
                <h3 class="text-xl font-semibold mb-3">Epic Medication List</h3>
                <textarea id="epicMedList"
                          rows="15"
                          class="w-full rounded-lg border border-gray-300 shadow-sm p-4"
                          placeholder="Paste Epic medication list here...">Aspirin 81 mg daily
Metformin 500 mg twice daily
Lisinopril 20 mg daily
Simvastatin 20 mg daily
Albuterol inhaler as needed
Omeprazole 40 mg daily
Hydrochlorothiazide 25 mg daily</textarea>
            </div>

            <!-- Differences & Final Reconciliation -->
            <div>
                <h3 class="text-xl font-semibold mb-3">Reconciliation</h3>
                <div id="comparisonResults"
                     class="border border-gray-300 bg-white rounded-lg shadow-sm min-h-[22rem] max-h-[36rem] overflow-auto p-4">
                    <p class="text-gray-500">Differences will appear here after you click "Compare Lists".</p>
                </div>
                <button onclick="compareLists()"
                        class="mt-4 bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded shadow">
                    Compare Lists
                </button>
                <button onclick="finalizeReconciliation()"
                        class="mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded shadow">
                    Finalize & Confirm
                </button>
            </div>
        </div>
    </div>

    <script>
        function parseMedList(text) {
            return text.split('\n').map(line => {
                const parts = line.trim().match(/(.+?)\s([\d\w\s]+)$/);
                return parts ? { name: parts[1].trim(), dosage: parts[2].trim(), original: line.trim() } : { name: line.trim(), dosage: '', original: line.trim() };
            }).filter(med => med.original);
        }

        function compareLists() {
            const facilityList = parseMedList(document.getElementById('facilityMedList').value);
            const epicList = parseMedList(document.getElementById('epicMedList').value);
            let differencesHtml = '';

            const medMap = {};

            facilityList.forEach(med => {
                if (!medMap[med.name]) medMap[med.name] = { facility: [], epic: [] };
                medMap[med.name].facility.push(med);
            });

            epicList.forEach(med => {
                if (!medMap[med.name]) medMap[med.name] = { facility: [], epic: [] };
                medMap[med.name].epic.push(med);
            });

            for (let medName in medMap) {
                const med = medMap[medName];
                let statusLabel = '', colorClass = '', details = '';

                if (med.facility.length && med.epic.length) {
                    const facilityDosage = med.facility[0].dosage;
                    const epicDosage = med.epic[0].dosage;

                    if (facilityDosage === epicDosage) {
                        statusLabel = 'Match';
                        colorClass = 'bg-green-50 border-green-400';
                        details = `${medName} ${facilityDosage}`;
                    } else {
                        statusLabel = 'Dosage Mismatch';
                        colorClass = 'bg-orange-50 border-orange-400';
                        details = `<div>
                            <span class="block font-semibold">${medName}</span>
                            <span>Facility Dosage: <strong>${facilityDosage}</strong></span><br>
                            <span>Epic Dosage: <strong>${epicDosage}</strong></span>
                            <select class="mt-2 border-gray-300 rounded reconcile-select w-full">
                                <option value="${med.facility[0].original}">Keep Facility Dosage (${facilityDosage})</option>
                                <option value="${med.epic[0].original}">Keep Epic Dosage (${epicDosage})</option>
                            </select>
                        </div>`;
                    }
                } else if (med.facility.length) {
                    statusLabel = 'Only in Facility';
                    colorClass = 'bg-red-50 border-red-400';
                    details = med.facility[0].original;
                } else if (med.epic.length) {
                    statusLabel = 'Only in Epic';
                    colorClass = 'bg-yellow-50 border-yellow-400';
                    details = med.epic[0].original;
                }

                differencesHtml += `
                    <div class="border-l-4 ${colorClass} p-3 mb-2 rounded shadow-sm">
                        ${details}
                        <span class="text-xs text-gray-500 italic ml-2">(${statusLabel})</span>
                        <button onclick="removeMed(this)" class="float-right text-xs text-red-500 hover:underline">Remove</button>
                    </div>
                `;
            }

            document.getElementById('comparisonResults').innerHTML = differencesHtml;
        }

        function removeMed(button) {
            button.parentElement.remove();
        }

        function finalizeReconciliation() {
            const finalMeds = [];
            document.querySelectorAll('#comparisonResults div').forEach(div => {
                const select = div.querySelector('.reconcile-select');
                if (select) {
                    finalMeds.push(select.value);
                } else {
                    const medText = div.querySelector('span.font-semibold') ?
                                    `${div.querySelector('span.font-semibold').textContent.trim()} ${div.textContent.split('Dosage:')[1].split('(')[0].trim()}` :
                                    div.textContent.trim();
                    finalMeds.push(medText);
                }
            });

            if (finalMeds.length === 0) {
                alert('No medications to finalize.');
                return;
            }

            alert('Final reconciled medications:\n\n' + finalMeds.join('\n'));
            // Send data to backend via AJAX if needed
        }
    </script>
</x-app-layout>
