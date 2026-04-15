const parkingForm = document.getElementById('parkingForm');
const parkedVehiclesBody = document.getElementById('parkedVehiclesBody');
const emptyStateRow = document.getElementById('emptyStateRow');

parkingForm.addEventListener('submit', function (event) {
  event.preventDefault();

  const vehicleNo = document.getElementById('vehicle_no').value.trim();
  const owner = document.getElementById('owner').value.trim();
  const contact = document.getElementById('contact').value.trim();
  const vehicleType = document.getElementById('vehicle_type').value.trim();
  const slot = document.getElementById('slot').value.trim();

  if (!vehicleNo || !owner || !contact || !vehicleType || !slot) {
    alert('Please fill in all fields before parking the vehicle.');
    return;
  }

  const entryTime = new Date().toLocaleString();

  console.log({
    vehicle_no: vehicleNo,
    owner,
    contact,
    vehicle_type: vehicleType,
    slot,
    entry_time: entryTime,
  });

  if (emptyStateRow) {
    emptyStateRow.remove();
  }

  const row = document.createElement('tr');
  row.innerHTML = `
    <td>${escapeHtml(vehicleNo)}</td>
    <td>${escapeHtml(owner)}</td>
    <td>${escapeHtml(contact)}</td>
    <td>${escapeHtml(vehicleType)}</td>
    <td>${escapeHtml(slot)}</td>
    <td>${escapeHtml(entryTime)}</td>
  `;

  parkedVehiclesBody.appendChild(row);
  parkingForm.reset();
});

function escapeHtml(value) {
  return value
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}