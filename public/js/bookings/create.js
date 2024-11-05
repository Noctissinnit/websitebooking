let isBookingPost = false;

$(document).ready(() => {
    generateCalendar();
    initTimepickers();
    updateDateTime();
    updateBookings();
    clearForms();
    tryGoogleCallback();
    updateCurrentAvailable();

    $("#select-room").select2({
        dropdownParent: $("#bookingModal"),
        width: "resolve",
    });
    $("#select-users").select2({
        dropdownParent: $("#bookingModal"),
        width: "resolve",
    });
    // $('#btn-add-booking').click(() => {
    //     const today = new Date();
    //     $('#form-booking>input[name="date"]').val(today.toISOString().substring(0,10));
    //     $("#loginModal").modal("show");
    // });
    $('#btn-history-add-booking').click(function(){
        if(tryGoogleCallback(true)) return;

        const url = new URL(googleLoginUrl);
        url.searchParams.set('bookings_room_id', roomId);
        url.searchParams.set('bookings_date', $('#form-booking>input[name="date"]').val());
        location.href = url.toString();
    });
    
    $("#form-login").submit(checkLogin);
    $('#btn-booking-form-close').click(resetSession);
    $('#form-booking').submit(async e => {
        e.preventDefault();
        if(isBookingPost) return location.reload();

        const formData = new FormData(e.currentTarget);
        if(!validateEmptyForm(formData, {
            'start_time': 'Jam Mulai',
            'start_time': 'Jam Selesai',
            'description': 'Deskripsi',
            'members': 'Peserta',
        })) return;

        if(isTimeLess(formData.get("end_time"), formData.get("start_time"))){
            alert("Jam Selesai tidak bisa kurang dari Jam Mulai.");
            return;
        }

        $('#loading').css('display', 'flex');
        isBookingPost = true;

        const rooms = await $.get(roomListUrl);
        let bookings = rooms.filter(dat => dat.id === roomId)[0].bookings;
        if(bookings.length > 0){
            const bookingsToday = bookings.filter(dat => isDateEqual(
                new Date($('#form-booking>input[name="date"]').val()),
                new Date(dat.date)));
            if(bookingsToday.some(dat => isTimeRangeOverlap(formData.get("start_time"), formData.get("end_time"), formatTime(dat.start_time), formatTime(dat.end_time)))){
                alert("Jam peminjaman sudah digunakan oleh user lain.");
                isBookingPost = false;
                $('#loading').css('display', 'none');
                return;
            }
        }
        await $.post($('#form-booking').attr('action'), $('#form-booking').serialize());
        location.reload();
    });
    
    $('button[data-bs-dismiss="modal"]').click(clearForms);

    $(document).on('keydown', function(e){
        if ((e.metaKey || e.ctrlKey) && e.key === 'h') {
            toggleOfficeMode();
        }
    });
});

async function showBookingHistory(date, dateStr){
    $('#bookingHistoryDate').html(dateStr);

    const url = new URL(listUrl);
    url.searchParams.set('date', dateStr);
    url.searchParams.set('room_id', roomId);

    const bookingsData = await $.get(url.toString());
    const tableBody = $('#bookingHistoryTable>tbody');

    tableBody.html('');
    if(bookingsData.length > 0){
        bookingsData.forEach((data, i) => {
            tableBody.append(`
                <tr>
                    <td>${data.department.name}</td>
                    <td>${formatTime(data.start_time)}</td>
                    <td>${formatTime(data.end_time)}</td>
                    <td>${data.description}</td>
                </tr>
            `)
        });
    } else {
        tableBody.html(`<tr><td colspan="7">Tidak ada data peminjaman...</td></tr>`)
    }

    $('#form-booking>input[name="date"]').val(dateStr);
    $('#form-booking-date').val(dateStr);
    if(!isOfficeMode) $('#btn-history-add-booking').css('display', isAtLeastOneDayLess(date, new Date()) ? 'none' : '');

    $('#bookingHistoryModal').modal('show');
}

function initTimepickers(){
    $('input.timepicker').datetimepicker({
        datepicker: false,
        format: 'H:i',
    })
}

function generateCalendar() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: $(window).width() > 768 ? 'dayGridMonth' : 'dayGridWeek',
        },
        initialView: $(window).width() > 768 ? 'dayGridMonth' : 'dayGridWeek',
        dateClick: function (info) {
            showBookingHistory(info.date, info.dateStr);
        },
        events: async function(info, successCallback, failureCallback) {
            try {
                // Fetch bookings within the view range
                const url = new URL(listUrl);
                url.searchParams.set('start', info.startStr);
                url.searchParams.set('end', info.endStr);
                url.searchParams.set('room_id', roomId);

                const bookings = await $.get(url.toString());
                const events = bookings.map(booking => {
                    const startTime = booking.start_time.slice(0, 5); // Format to HH:mm
                    const endTime = booking.end_time.slice(0, 5);     // Format to HH:mm
                    const title = `${startTime} - ${endTime}: ${booking.description}`;
                    const color = getRandomColor(); // Assign a random color to each event
                    return {
                        title: title,
                        start: `${booking.date}T${booking.start_time}`,
                        end: `${booking.date}T${booking.end_time}`,
                        color: color, // Use random color for background
                        extendedProps: {
                            department: booking.department,
                            description: booking.description,
                            start_time: startTime,
                            end_time: endTime,
                            user_name: booking.user.name,
                        }
                    };
                });
                
                successCallback(events);
            } catch (error) {
                console.error('Error fetching bookings:', error);
                failureCallback(error);
            }
        },
        eventClick: function(info) {
            const booking = info.event.extendedProps;
            const bookingDetailsHtml = `
                <p><strong>Booked by:</strong> ${booking.user_name}</p>
                <p><strong>Department:</strong> ${booking.department}</p>
                <p><strong>Time:</strong> ${booking.start_time} - ${booking.end_time}</p>
                <p><strong>Description:</strong> ${booking.description}</p>
            `;
            $('#bookingDetailsModal .modal-body').html(bookingDetailsHtml);
            $('#bookingDetailsModal').modal('show');
        },
    });
    calendar.render();
}

// Function to generate a random color
function getRandomColor() {
    const colors = [
        '#FF5733', '#33FF57', '#3357FF', '#FF33A8', '#A833FF', '#FFD133', '#33FFDB'
    ];
    return colors[Math.floor(Math.random() * colors.length)];
}

// Function to generate a random color
function getRandomColor() {
    const colors = [
        '#FF5733', '#33FF57', '#3357FF', '#FF33A8', '#A833FF', '#FFD133', '#33FFDB'
    ];
    return colors[Math.floor(Math.random() * colors.length)];
}



function isToday(dateString) {
    // Create a Date object from the input string
    const inputDate = new Date(dateString);

    // Get today's date
    const today = new Date();
    
    return isDateEqual(inputDate, today);
}

function isDateEqual(date1, date2){
    // Check if the input date is today by comparing the year, month, and day
    return date1.getFullYear() === date2.getFullYear() &&
           date1.getMonth() === date2.getMonth() &&
           date1.getDate() === date2.getDate();
}

function isTimeRangeOverlap(start1, end1, start2, end2) {
    // Convert times to Date objects for comparison
    const formatTime = (time) => new Date(`1970-01-01T${time}:00`);

    const startTime1 = formatTime(start1);
    const endTime1 = formatTime(end1);
    const startTime2 = formatTime(start2);
    const endTime2 = formatTime(end2);

    // Check if the two time ranges overlap
    return startTime1 < endTime2 && startTime2 < endTime1;
}

function isAtLeastOneDayLess(date1, date2) {
    const differenceInTime = date2.getTime() - date1.getTime();
    const oneDayInMilliseconds = 24 * 60 * 60 * 1000;
    return differenceInTime > oneDayInMilliseconds;
}

function isTimeLess(time1, time2) {
  // Parse the times as hours and minutes (assuming "HH:mm" format)
  const [hours1, minutes1] = time1.split(':').map(Number);
  const [hours2, minutes2] = time2.split(':').map(Number);

  // Create Date objects for comparison
  const date1 = new Date();
  date1.setHours(hours1, minutes1);

  const date2 = new Date();
  date2.setHours(hours2, minutes2);

  // Compare the two times
  return date1 < date2;
}

function updateDateTime() {
    const now = new Date();
    const options = {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    };
    const formattedDate = now.toLocaleDateString("id-ID", options);
    const formattedTime = now.toLocaleTimeString("id-ID");

    document.getElementById("current-date").innerText = formattedDate;
    document.getElementById("current-time").innerText = formattedTime;
}

async function updateBookings() {
    const url = new URL(listUrl);
    url.searchParams.set('date', new Date().toISOString().substring(0,10));
    url.searchParams.set('room_id', roomId);

    const bookingsData = await $.get(url.toString());

    const currentBookingsDiv = $("#current-bookings");
    currentBookingsDiv.html("<h4>Jam Penggunaan Hari Ini:</h4>");

    if (bookingsData.length === 0) {
        currentBookingsDiv.append("<p>Tidak ada peminjaman hari ini.</p>");
    } else {
        bookingsData.forEach((booking, index) => {
            currentBookingsDiv.append(`
            <div>
                <span>Dipinjam oleh ${booking.user.name} dari ${formatTime(booking.start_time)} hingga ${formatTime(booking.end_time)}</span>
                ${isAuth ? `<a href="${destroyUrl}?id=${booking.id}"><button class="btn btn-danger btn-sm ml-2">Hapus</button></a>` : ""}
            </div>
            `);
        });
    }
}

function formatTime(time) {
    let parts = time.split(":");
    if (parts.length === 3) {
        return parts.slice(0, 2).join(":");
    }
    return time;
}

async function checkLogin(e) {
    e.preventDefault();

    const nis = document.getElementById("login-nis").value;
    const password = document.getElementById("login-password").value;

    const res = await $.post(loginUrl, { nis, password });
    if (res.success) {
        $('#form-booking>input[name="room_id"]').val(roomId);
        $('#form-booking>input[name="nis"]').val(nis);
        $('#form-booking>input[name="password"]').val(password);
        $('#form-booking>input[name="nama"]').val(res.data.name);
        $('#form-booking>input[name="email"]').val(res.data.email);
        $('#form-booking>input[name="department_id"]').val(res.data.department.id);
        $('#booking-user-department').val(res.data.department.name);

        $("#loginModal").modal("hide");
        $("#bookingModal").modal("show");
    } else {
        alert("NIS atau Password salah. Silakan coba lagi.");
    }
}

function clearForms(){
    $('#form-login')[0].reset();
    $('#form-booking')[0].reset();
}

function tryGoogleCallback(isLoggedIn = true){
    if(!isGoogleCallback) return false;
    
    if(isLoggedIn) {
        $('#form-booking-date').val($('#form-booking>input[name="date"]').val());
    } else {
        $('#form-booking>input[name="date"]').val(bookingsDate);
        $('#form-booking-date').val(bookingsDate);
    }
    $('#bookingHistoryModal').modal('hide');
    $('#bookingModal').modal('show');

    return true;
}

function toggleOfficeMode(){
    isOfficeMode = !isOfficeMode;

    if(isOfficeMode){
        $('#btn-history-add-booking').css('display', 'none');
    }
}

async function resetSession(){
    await $.get(resetSessionUrl);
}

async function updateCurrentAvailable(){
    const res = await $.get(roomAvailableUrl);
    $('#current-available-status').html(res.available ? 'Tersedia' : 'Tidak Tersedia');
}

setInterval(updateDateTime, 1000);
setInterval(updateCurrentAvailable, 1000);
setInterval(updateBookings, 5000);
