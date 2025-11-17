document.addEventListener('DOMContentLoaded', () => {
    // Add a simple prototype for Date to add minutes
    Date.prototype.addMinutes = function(m) {
        this.setTime(this.getTime() + (m*60*1000));
        return this;
    }
    
    const translations = {
        'zh-TW': {
            title: '物理治療平台',
            nav_home: '首頁', nav_services: '服務項目', nav_therapists: '治療師', nav_booking: '線上預約',
            nav_my_appointments: '我的預約',
            nav_admin_dashboard: '後台管理',
            nav_my_schedule: '我的排班',
            nav_doctor_dashboard: '病歷管理',
            nav_clinical_system: '臨床系統',
            hero_title: '專業、便捷的物理治療服務', hero_subtitle: '立即尋找適合您的治療師，開始您的康復之旅。', hero_cta: '立即預約',
            services_title: '我們的服務',
            service1_title: '物理治療評估', service1_desc: '全面的身體功能評估，找出問題根源。',
            service2_title: '徒手治療', service2_desc: '透過專業手法，放鬆緊繃肌肉，改善關節活動度。',
            service3_title: '運動治療', service3_desc: '設計個人化運動處方，強化肌力，預防復發。',
            therapists_title: '我們的治療師',
            booking_title: '線上預約',
            available_slots: '可預約時段',
            doctor_dashboard_title: '病歷管理',
            clinical_system_title: '臨床輔助系統',
            select_body_part: '選擇部位',
            admin_dashboard_title: '後台管理系統',
            add_doctor_title: '新增醫師',
            doctor_name_label: '醫師姓名：', specialty_label: '專長：', add_button: '新增',
            schedule_management_title: '排班管理',
            user_management_title: '使用者管理',
            login: '登入', register: '註冊', logout: '登出', password: '密碼', username: '使用者名稱',
            login_title: '登入您的帳戶', register_title: '建立新帳戶',
            footer_text: '&copy; 2025 物理治療平台. All rights reserved.',
            admin_schedule_title: '排班與預約總覽',
            today_patients: '今日病患',
            record_details: '病歷詳情',
            select_patient_prompt: '請從左側選擇一位病患以查看或編輯病歷。',
            schedule_details_title: '排程細節',
            select_date_prompt: '請從左側選擇日期以查看排程。',
            manage_schedule_title: '管理排班',
            manage_slots_title: '管理可預約時段',
            select_date_label: '選擇日期:',
            add_slot_button: '新增時段',
            welcome_message: '歡迎,',
            date_label: '日期',
            time_label: '時間',
            doctor_label: '醫師',
            cancel_appointment_btn: '取消預約'
        },
        'zh-CN': {
            title: '物理治疗平台',
            nav_home: '首页', nav_services: '服务项目', nav_therapists: '治疗师', nav_booking: '在线预约',
            nav_my_appointments: '我的预约',
            nav_doctor_dashboard: '病历管理', nav_clinical_system: '临床系统', nav_admin_dashboard: '后台管理',
            nav_my_schedule: '我的排班',
            hero_title: '专业、便捷的物理治疗服务', hero_subtitle: '立即寻找适合您的治疗师，开始您的康复之旅。', hero_cta: '立即预约',
            services_title: '我们的服务',
            service1_title: '物理治疗评估', service1_desc: '全面的身体功能评估，找出问题根源。',
            service2_title: '徒手治疗', service2_desc: '透过专业手法，放松紧绷肌肉，改善关节活动度。',
            service3_title: '运动治疗', service3_desc: '设计个人化运动处方，强化肌力，预防复发。',
            therapists_title: '我们的治疗师',
            booking_title: '在线预约',
            available_slots: '可预约时段',
            doctor_dashboard_title: '病历管理',
            clinical_system_title: '临床辅助系统',
            select_body_part: '选择部位',
            admin_dashboard_title: '后台管理系统',
            add_doctor_title: '新增医师',
            doctor_name_label: '医师姓名：', specialty_label: '专长：', add_button: '新增',
            schedule_management_title: '排班管理',
            user_management_title: '用户管理',
            login: '登录', register: '注册', logout: '登出', password: '密码', username: '用户名',
            login_title: '登录您的帐户', register_title: '创建新帐户',
            footer_text: '&copy; 2025 物理治疗平台. All rights reserved.',
            admin_schedule_title: '排班与预约总览',
            today_patients: '今日病患',
            record_details: '病历详情',
            select_patient_prompt: '请从左侧选择一位病患以查看或编辑病历。',
            schedule_details_title: '排程细节',
            select_date_prompt: '请从左側选择日期以查看排程。',
            manage_schedule_title: '管理排班',
            manage_slots_title: '管理可预约时段',
            select_date_label: '选择日期:',
            add_slot_button: '新增时段',
            welcome_message: '歡迎,',
            date_label: '日期',
            time_label: '时间',
            doctor_label: '医师',
            cancel_appointment_btn: '取消预约'
        },
        'en': {
            title: 'Physiotherapy Platform',
            nav_home: 'Home', nav_services: 'Services', nav_therapists: 'Therapists', nav_booking: 'Online Booking',
            nav_my_appointments: 'My Appointments',
            nav_doctor_dashboard: 'Medical Records', nav_clinical_system: 'Clinical System', nav_admin_dashboard: 'Admin Dashboard',
            nav_my_schedule: 'My Schedule',
            hero_title: 'Professional and Convenient Physiotherapy Services', hero_subtitle: 'Find the right therapist for you and start your recovery journey now.', hero_cta: 'Book Now',
            services_title: 'Our Services',
            service1_title: 'Physiotherapy Assessment', service1_desc: 'Comprehensive assessment of physical function to identify the root cause of problems.',
            service2_title: 'Manual Therapy', service2_desc: 'Relax tense muscles and improve joint mobility through professional techniques.',
            service3_title: 'Exercise Therapy', service3_desc: 'Design personalized exercise prescriptions to strengthen muscles and prevent recurrence.',
            therapists_title: 'Our Therapists',
            booking_title: 'Online Booking',
            available_slots: 'Available Slots',
            doctor_dashboard_title: 'Medical Record Management',
            clinical_system_title: 'Clinical Support System',
            select_body_part: 'Select Body Part',
            admin_dashboard_title: 'Admin Management System',
            add_doctor_title: 'Add Doctor',
            doctor_name_label: 'Doctor Name:', specialty_label: 'Specialty:', add_button: 'Add',
            schedule_management_title: 'Schedule Management',
            user_management_title: 'User Management',
            login: 'Login', register: 'Register', logout: 'Logout', password: 'Password', username: 'Username',
            login_title: 'Login to Your Account', register_title: 'Create a New Account',
            footer_text: '&copy; 2025 Physiotherapy Platform. All rights reserved.',
            admin_schedule_title: 'Schedule & Appointment Overview',
            today_patients: 'Today\'s Patients',
            record_details: 'Record Details',
            select_patient_prompt: 'Select a patient from the left to view or edit records.',
            schedule_details_title: 'Schedule Details',
            select_date_prompt: 'Select a date from the left to view the schedule.',
            manage_schedule_title: 'Manage Schedule',
            manage_slots_title: 'Manage Available Slots',
            select_date_label: 'Selected Date:',
            add_slot_button: 'Add Slot',
            welcome_message: 'Welcome,',
            date_label: 'Date',
            time_label: 'Time',
            doctor_label: 'Doctor',
            cancel_appointment_btn: 'Cancel Appointment'
        }
    };

    // --- STATE MANAGEMENT ---
    const state = {
        token: null,
        user: null, // Decoded user info from JWT
        doctors: [],
        patients: [],
        medicalRecords: [],
        appointments: [],
        acupuncturePoints: {},
        calendarDate: new Date(),
        adminCalendarDate: new Date(),
        scheduleCalendarDate: new Date(),
        myScheduleCalendarDate: new Date(),
        selectedDoctorId: null,
    };

    const API_BASE_URL = '/ukn111534131/Backend/api';
    const GOOGLE_CLIENT_ID = '1180508929-1oneb26vknepibg9v0o45ofjtc7tgkcs.apps.googleusercontent.com';

    // --- DOM ELEMENTS ---
    const mainNav = document.getElementById('main-nav');
    const authContainer = document.getElementById('auth-container');
    const userWelcome = document.getElementById('user-welcome');
    const loginBtn = document.getElementById('login-btn');
    const registerBtn = document.getElementById('register-btn');
    const logoutBtn = document.getElementById('logout-btn');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const medicalRecordForm = document.getElementById('medical-record-form');
    const medicalRecordIdInput = document.getElementById('medical-record-id');
    const medicalRecordPatientIdInput = document.getElementById('medical-record-patient-id');
    const recordDiagnosisInput = document.getElementById('record-diagnosis');
    const recordTreatmentInput = document.getElementById('record-treatment');
    const recordNotesInput = document.getElementById('record-notes');
    const newMedicalRecordBtn = document.getElementById('new-medical-record-btn');
    const availableDoctorsList = document.getElementById('available-doctors-list');
    const sections = document.querySelectorAll('main > section');

    // --- MODAL INSTANCES ---
    let loginModal, registerModal, scheduleModal;
    if (document.getElementById('login-modal')) {
        loginModal = new bootstrap.Modal(document.getElementById('login-modal'));
    }
    if (document.getElementById('register-modal')) {
        registerModal = new bootstrap.Modal(document.getElementById('register-modal'));
    }
    if (document.getElementById('schedule-modal')) {
        scheduleModal = new bootstrap.Modal(document.getElementById('schedule-modal'));
    }


    // --- LANGUAGE SWITCHER ---
    const languageSelect = document.getElementById('language-select');
    function changeLanguage(lang = 'zh-TW') {
        document.querySelectorAll('[data-lang]').forEach(element => {
            const key = element.getAttribute('data-lang');
            const translation = translations[lang]?.[key] || translations['zh-TW'][key];
            if (translation) {
                if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                    element.placeholder = translation;
                } else {
                    element.innerHTML = translation;
                }
            }
        });
        document.documentElement.lang = lang;
        updateUIForAuthState(); // Re-update UI for dynamic elements
    }
    languageSelect.addEventListener('change', (e) => changeLanguage(e.target.value));

    // --- AUTHENTICATION ---
    function parseJwt(token) {
        try {
            const base64Url = token.split('.')[1];
            const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
            const jsonPayload = decodeURIComponent(atob(base64).split('').map(c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)).join(''));
            const decoded = JSON.parse(jsonPayload);
            const roleClaim = decoded['http://schemas.microsoft.com/ws/2008/06/identity/claims/role'];
            return {
                id: decoded.sub,
                email: decoded.email,
                name: decoded.name,
                role: Array.isArray(roleClaim) ? roleClaim[0] : roleClaim
            };
        } catch (e) {
            console.error("Failed to parse JWT", e);
            return null;
        }
    }

    function saveToken(token) {
        state.token = token;
        localStorage.setItem('jwt_token', token);
        state.user = parseJwt(token);
    }
    function clearToken() {
        state.token = null;
        state.user = null;
        localStorage.removeItem('jwt_token');
    }

    async function fetchDoctorInfo() {
        if (state.user && state.user.role === 'Doctor') {
            try {
                const response = await fetchWithAuth(`${API_BASE_URL}/get_doctor_by_user.php?id=${state.user.id}`);
                if (response.ok) {
                    const doctor = await response.json();
                    state.user.doctorId = doctor.id;
                } else {
                    console.error('Failed to fetch doctor info for user');
                }
            } catch (error) {
                console.error('Error fetching doctor info:', error);
            }
        }
    }

    async function fetchPatientInfo() {
        if (state.user && state.user.role === 'User') {
            try {
                const response = await fetchWithAuth(`${API_BASE_URL}/patients/user/${state.user.id}`);
                if (response.ok) {
                    const patient = await response.json();
                    state.user.patientId = patient.id;
                } else {
                    console.error('Failed to fetch patient info for user');
                }
            } catch (error) {
                console.error('Error fetching patient info:', error);
            }
        }
    }

    async function fetchWithAuth(url, options = {}) {
        const headers = { ...options.headers };
        if (state.token) {
            headers['Authorization'] = `Bearer ${state.token}`;
        }
        if (!headers['Content-Type'] && options.body) {
            headers['Content-Type'] = 'application/json';
        }

        const response = await fetch(url, { ...options, headers });

        if (response.status === 401) {
            handleLogout();
            alert('您的連線已逾時，請重新登入。');
            throw new Error('Unauthorized');
        }
        return response;
    }

    // --- UI UPDATES ---
    function updateUIForAuthState() {
        const lang = languageSelect.value || 'zh-TW';
        if (state.user) {
            userWelcome.innerHTML = `${translations[lang]?.welcome_message || '歡迎,'} ${state.user.name}`;
            userWelcome.classList.remove('hidden');
            loginBtn.classList.add('hidden');
            registerBtn.classList.add('hidden');
            logoutBtn.classList.remove('hidden');

            document.querySelectorAll('#main-nav .nav-item[data-role]').forEach(item => {
                const allowedRoles = JSON.parse(item.getAttribute('data-role'));
                const userRole = state.user.role.toLowerCase();
                const isVisible = userRole === 'admin' || allowedRoles.includes(userRole);
                item.classList.toggle('hidden', !isVisible);
            });
            
            const defaultSection = state.user.role === 'Admin' ? 'admin-dashboard' : (state.user.role === 'Doctor' ? 'my-schedule-page' : 'home');
            showSection(defaultSection);

        } else {
            userWelcome.classList.add('hidden');
            loginBtn.classList.remove('hidden');
            registerBtn.classList.remove('hidden');
            logoutBtn.classList.add('hidden');

            document.querySelectorAll('#main-nav .nav-item[data-role]').forEach(item => {
                const allowedRoles = JSON.parse(item.getAttribute('data-role'));
                // Show public items only for non-logged-in users
                item.classList.toggle('hidden', !allowedRoles.includes('user'));
            });
            showSection('home');
        }
    }

    // --- EVENT HANDLERS ---
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const username = document.getElementById('register-username').value;
        const email = document.getElementById('register-email').value;
        const password = document.getElementById('register-password').value;

        try {
            const response = await fetch(`${API_BASE_URL}/register.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, email, password })
            });

            if (response.ok) {
                alert('註冊成功！請登入。');
                registerModal.hide();
                loginModal.show();
            } else {
                const error = await response.text();
                alert(`註冊失敗: ${error}`);
            }
        } catch (err) {
            alert(`發生錯誤: ${err.message}`);
        }
    });

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;
        await handleLogin({ email, password });
    });
    
    async function handleLogin(credentials) {
         try {
            const response = await fetch(`${API_BASE_URL}/login.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(credentials)
            });

            if (response.ok) {
                const { token } = await response.json();
                saveToken(token);
                await fetchDoctorInfo();
                await fetchPatientInfo();
                updateUIForAuthState();
                loginModal.hide();
            } else {
                alert('登入失敗：Email或密碼無效。');
            }
        } catch (err) {
            alert(`發生錯誤: ${err.message}`);
        }
    }

    async function handleGoogleCredentialResponse(response) {
        try {
            const res = await fetch(`${API_BASE_URL}/google-login.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ credential: response.credential })
            });

            if (res.ok) {
                const { token } = await res.json();
                saveToken(token);
                await fetchDoctorInfo();
                await fetchPatientInfo();
                updateUIForAuthState();
                loginModal.hide();
            } else {
                alert('Google 登入失敗。');
            }
        } catch (err) {
            alert(`Google 登入時發生錯誤: ${err.message}`);
        }
    }

    logoutBtn.addEventListener('click', () => handleLogout());

    function handleLogout() {
        clearToken();
        updateUIForAuthState();
    }

    medicalRecordForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const recordId = medicalRecordIdInput.value;
        const patientId = medicalRecordPatientIdInput.value;
        const diagnosis = recordDiagnosisInput.value;
        const treatment = recordTreatmentInput.value;
        const notes = recordNotesInput.value;

        const doctorId = state.user.doctorId; 
        if (!doctorId) {
            alert('錯誤：找不到醫師ID，請重新登入再試。');
            return;
        }

        const recordData = {
            patientId: parseInt(patientId),
            diagnosis,
            treatment,
            notes,
            doctorId: doctorId
        };

        try {
            let response;
            if (recordId) {
                response = await fetchWithAuth(`${API_BASE_URL}/medical_records.php?id=${recordId}`, {
                    method: 'PUT',
                    body: JSON.stringify(recordData)
                });
            } else {
                response = await fetchWithAuth(`${API_BASE_URL}/medical_records.php`, {
                    method: 'POST',
                    body: JSON.stringify(recordData)
                });
            }

            if (response.ok) {
                alert('病歷儲存成功！');
                renderDoctorDashboard(); 
            } else {
                alert(`病歷儲存失敗: ${await response.text()}`);
            }
        } catch (error) {
            alert(`發生錯誤：${error.message}`);
        }
    });

    // --- PAGE NAVIGATION ---
    function showSection(sectionId) {
        sections.forEach(section => section.classList.remove('active'));
        const activeSection = document.getElementById(sectionId);
        if (activeSection) {
            activeSection.classList.add('active');
            // Trigger render functions when a section is shown
            if (sectionId === 'therapists') renderTherapistsPage();
            if (sectionId === 'admin-dashboard') renderAdminDashboard();
            if (sectionId === 'doctor-dashboard') renderDoctorDashboard();
            if (sectionId === 'my-schedule-page') renderMySchedulePage();
            if (sectionId === 'my-appointments-page') renderMyAppointmentsPage();
            if (sectionId === 'booking') renderBookingPage();
        }
    }

    document.querySelectorAll('#main-nav a[data-section], .cta-button[data-section]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const sectionId = link.getAttribute('data-section');
            showSection(sectionId);
        });
    });

    // --- DATA FETCHING & RENDERING ---
    async function getDoctors() {
        try {
            const response = await fetchWithAuth(`${API_BASE_URL}/get_all_doctors.php`);
            if (!response.ok) {
                // Try to get a more descriptive error from the response body
                const errorText = await response.text();
                throw new Error(`Failed to fetch doctors: ${response.status} ${response.statusText} - ${errorText}`);
            }
            state.doctors = await response.json();
            return state.doctors;
        } catch (error) { 
            console.error('Error fetching doctors:', error); 
            // Propagate the error to the caller so it can be handled in the UI
            throw error; 
        }
    }

    async function getPatients() {
        try {
            const response = await fetchWithAuth(`${API_BASE_URL}/get_all_patients.php`);
            if (!response.ok) throw new Error('Failed to fetch patients');
            state.patients = await response.json();
            return state.patients;
        } catch (error) { console.error('Error fetching patients:', error); return []; }
    }
    
    async function addDoctor(doctor) {
        try {
            const response = await fetchWithAuth(`${API_BASE_URL}/doctors_crud.php`, { method: 'POST', body: JSON.stringify(doctor) });
            if (!response.ok) { alert(`新增失敗: ${await response.text()}`); return null; }
            return await response.json();
        } catch (error) { alert(`發生錯誤：${error.message}`); return null; }
    }

    async function deleteDoctor(doctorId) {
        try {
            const response = await fetchWithAuth(`${API_BASE_URL}/doctors_crud.php?id=${doctorId}`, { method: 'DELETE' });
            return response.ok;
        } catch (error) { console.error(`發生錯誤：${error.message}`); return false; }
    }

    async function getMedicalRecords() {
        try {
            const response = await fetchWithAuth(`${API_BASE_URL}/medical_records.php`);
            if (!response.ok) throw new Error('Failed to fetch medical records');
            state.medicalRecords = await response.json();
            return state.medicalRecords;
        } catch (error) { console.error('Error fetching medical records:', error); return []; }
    }

    async function getUsers() {
        try {
            const response = await fetchWithAuth(`${API_BASE_URL}/get_users.php`);
            if (!response.ok) throw new Error('Failed to fetch users');
            return await response.json();
        } catch (error) { console.error('Error fetching users:', error); return []; }
    }

    async function updateUserRole(userId, role) {
        try {
            const response = await fetchWithAuth(`${API_BASE_URL}/update_user_role.php?id=${userId}`, { method: 'PUT', body: JSON.stringify({ role }) });
            return response.ok;
        } catch (error) { console.error('Error updating user role:', error); return false; }
    }

    async function renderMyAppointmentsPage() {
        const container = document.getElementById('my-appointments-list');
        if (!container) return;
        container.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

        try {
            const response = await fetchWithAuth(`${API_BASE_URL}/my_appointments.php`);
            if (!response.ok) throw new Error('無法獲取預約');
            const appointments = await response.json();

            if (appointments.length === 0) {
                container.innerHTML = '<p class="text-center text-muted">您目前沒有任何預約。</p>';
                return;
            }

            const lang = languageSelect.value;
            const appointmentCards = appointments.map(app => {
                const appointmentTime = new Date(app.appointmentTime);
                const date = appointmentTime.toLocaleDateString();
                const time = appointmentTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                const cancellationHours = app.cancellationPolicyHours;

                const now = new Date();
                const hoursDifference = (appointmentTime.getTime() - now.getTime()) / (1000 * 60 * 60);
                const canCancel = hoursDifference > cancellationHours;

                return `
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <p class="card-text"><strong>${translations[lang]?.date_label || '日期'}:</strong> ${date}</p>
                            <p class="card-text"><strong>${translations[lang]?.time_label || '時間'}:</strong> ${time}</p>
                            <p class="card-text"><strong>${translations[lang]?.doctor_label || '醫師'}:</strong> ${app.doctorName}</p>
                            ${canCancel
                                ? `<button class="btn btn-sm btn-outline-danger cancel-appointment-btn" data-appointment-id="${app.id}">${translations[lang]?.cancel_appointment_btn || '取消預約'}</button>`
                                : `<p class="text-muted small mt-2">預約前${cancellationHours}小時內無法取消</p>`
                            }
                        </div>
                    </div>
                </div>
            `}).join('');
            container.innerHTML = appointmentCards;

            container.querySelectorAll('.cancel-appointment-btn').forEach(button => {
                button.addEventListener('click', async (e) => {
                    const appointmentId = e.target.dataset.appointmentId;
                    if (confirm('您確定要取消這個預約嗎？')) {
                        try {
                            const deleteResponse = await fetchWithAuth(`${API_BASE_URL}/appointments.php?id=${appointmentId}`, { method: 'DELETE' });
                            if (deleteResponse.ok) {
                                alert('預約已取消。');
                                renderMyAppointmentsPage(); // Refresh
                            } else {
                                alert(`取消失敗: ${await deleteResponse.text()}`);
                            }
                        } catch (error) { alert(`發生錯誤：${error.message}`); }
                    }
                });
            });

        } catch (error) {
            container.innerHTML = `<p class="text-center text-danger">${error.message}</p>`;
        }
    }

    async function renderTherapistsPage() {
        const container = document.getElementById('therapist-cards-container');
        if (!container) return;
        container.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        await getDoctors();
        container.innerHTML = '';
        state.doctors.forEach((doc) => {
            const col = document.createElement('div');
            col.className = 'col-md-6 col-lg-4';
            col.innerHTML = `
                <div class="card h-100 shadow-sm text-center">
                    <img src="assets/images/doctor1.png" class="card-img-top" alt="${doc.name}">
                    <div class="card-body">
                        <h3 class="card-title h5">${doc.name}</h3>
                        <p class="card-text text-muted">${doc.specialty}</p>
                        <button class="btn btn-primary book-now-btn" data-doctor-id="${doc.id}" data-section="booking">立即預約</button>
                    </div>
                </div>`;
            container.appendChild(col);
        });
        // Re-add event listener for the newly created buttons
        container.querySelectorAll('.book-now-btn').forEach(btn => {
            btn.addEventListener('click', (e) => showSection(e.currentTarget.dataset.section));
        });
    }

    function renderBookingPage() {
        const calendarGrid = document.getElementById('calendar-grid');
        const monthYearDisplay = document.getElementById('month-year');
        const prevMonthBtn = document.getElementById('prev-month');
        const nextMonthBtn = document.getElementById('next-month');

        const renderCalendar = () => {
            calendarGrid.innerHTML = '';
            const date = state.calendarDate;
            const year = date.getFullYear();
            const month = date.getMonth();

            monthYearDisplay.textContent = `${year}年 ${month + 1}月`;

            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            const dayNames = ['日', '一', '二', '三', '四', '五', '六'];
            dayNames.forEach(name => {
                const dayNameEl = document.createElement('div');
                dayNameEl.className = 'day-name';
                dayNameEl.textContent = name;
                calendarGrid.appendChild(dayNameEl);
            });

            for (let i = 0; i < firstDay; i++) {
                calendarGrid.appendChild(document.createElement('div')).className = 'day empty';
            }

            for (let i = 1; i <= daysInMonth; i++) {
                const dayEl = document.createElement('div');
                dayEl.className = 'day';
                dayEl.textContent = i;
                const fullDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                dayEl.dataset.date = fullDate;
                
                dayEl.addEventListener('click', (e) => {
                    calendarGrid.querySelectorAll('.day').forEach(d => d.classList.remove('selected'));
                    e.target.classList.add('selected');
                    renderAvailableDoctors(fullDate);
                });
                calendarGrid.appendChild(dayEl);
            }
        };

        prevMonthBtn.onclick = () => {
            state.calendarDate.setMonth(state.calendarDate.getMonth() - 1);
            renderCalendar();
        };

        nextMonthBtn.onclick = () => {
            state.calendarDate.setMonth(state.calendarDate.getMonth() + 1);
            renderCalendar();
        };

        renderCalendar();
        availableDoctorsList.innerHTML = '<p class="text-muted">請先在左側選擇日期。</p>';
    }

    async function renderAvailableDoctors(date) {
        availableDoctorsList.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        try {
            const response = await fetchWithAuth(`${API_BASE_URL}/get_available_doctors.php?date=${date}`);
            if (!response.ok) throw new Error('Failed to fetch available doctors');
            const doctors = await response.json();

            if (doctors.length === 0) {
                availableDoctorsList.innerHTML = '<p class="text-muted">當天沒有可預約的醫生。</p>';
                return;
            }

            availableDoctorsList.innerHTML = doctors.map(doctor => `
                <div class="available-doctor-card">
                    <h4 class="h6">${doctor.name} <small class="text-muted">(${doctor.specialty})</small></h4>
                    <div class="available-slots-for-doctor">
                        ${doctor.availableSlots.map(slot => `
                            <button class="time-slot-btn" data-doctor-id="${doctor.id}" data-date="${date}" data-time="${slot.substring(0, 5)}">
                                ${slot.substring(0, 5)}
                            </button>
                        `).join('')}
                    </div>
                </div>
            `).join('');

            availableDoctorsList.querySelectorAll('.time-slot-btn').forEach(button => {
                button.addEventListener('click', async (e) => {
                    const { doctorId, date, time } = e.target.dataset;
                    if (!state.user) {
                        alert('請先登入才能預約。');
                        loginModal.show();
                        return;
                    }

                    let patientId;
                    // If the user is a doctor, they must select a patient
                    if (state.user.role === 'Doctor') {
                        await getPatients(); // Make sure we have the patient list
                        if (!state.patients || state.patients.length === 0) {
                            alert('沒有可用的病患資料。請先新增病患。');
                            return;
                        }

                        // Create a dynamic select prompt
                        const patientOptions = state.patients.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
                        const promptContainer = document.createElement('div');
                        promptContainer.innerHTML = `
                            <p>請為您的病患預約：</p>
                            <select id="patient-select-prompt" class="form-select">
                                <option value="">請選擇一位病患...</option>
                                ${patientOptions}
                            </select>
                        `;
                        
                        // Simple custom "confirm" by replacing alert/confirm
                        const patientSelectionModal = new bootstrap.Modal(document.getElementById('patient-selection-modal'), {
                            keyboard: false,
                            backdrop: 'static'
                        });
                        document.getElementById('patient-selection-modal-body').innerHTML = '';
                        document.getElementById('patient-selection-modal-body').appendChild(promptContainer);
                        patientSelectionModal.show();

                        document.getElementById('confirm-patient-selection').onclick = () => {
                            const selectedPatientId = document.getElementById('patient-select-prompt').value;
                            if (selectedPatientId) {
                                patientId = parseInt(selectedPatientId);
                                patientSelectionModal.hide();
                                proceedWithBooking(doctorId, patientId, date, time);
                            } else {
                                alert('請選擇一位病患。');
                            }
                        };
                        return; // Stop execution until patient is selected from modal

                    } else {
                        patientId = state.user.patientId;
                        if (!patientId) {
                             alert('您的病患資料不完整，無法預約。');
                             return;
                        }
                         if (confirm(`確定要預約 ${date} ${time} 的時段嗎？`)) {
                            proceedWithBooking(doctorId, patientId, date, time);
                        }
                    }
                });
            });

            async function proceedWithBooking(doctorId, patientId, date, time) {
                 try {
                    const appointmentDateTime = `${date}T${time}:00`;
                    const bookResponse = await fetchWithAuth(`${API_BASE_URL}/appointments.php`, {
                        method: 'POST',
                        body: JSON.stringify({
                            doctorId: parseInt(doctorId),
                            patientId: patientId,
                            appointmentTime: appointmentDateTime
                        })
                    });
                    if (bookResponse.ok) {
                        alert('預約成功！');
                        renderAvailableDoctors(date); // Refresh slots
                    } else {
                        alert(`預約失敗: ${await bookResponse.text()}`);
                    }
                } catch (error) { alert(`發生錯誤：${error.message}`); }
            }

        } catch (error) {
            availableDoctorsList.innerHTML = `<p class="text-danger">${error.message}</p>`;
        }
    }

    async function renderUserManagement() {
        const container = document.getElementById('user-list-container');
        if (!container) return;
        container.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
        const users = await getUsers();
        container.innerHTML = '';
        users.forEach(user => {
            if (user.email === state.user.email) return;
            const userEl = document.createElement('div');
            userEl.className = 'admin-list-item';
            userEl.innerHTML = `<span>${user.username} (${user.email})</span>
                <div class="role-manager d-flex gap-2">
                    <select class="form-select form-select-sm" data-user-id="${user.id}">
                        <option value="User" ${user.role === 'User' ? 'selected' : ''}>User</option>
                        <option value="Doctor" ${user.role === 'Doctor' ? 'selected' : ''}>Doctor</option>
                        <option value="Admin" ${user.role === 'Admin' ? 'selected' : ''}>Admin</option>
                    </select>
                    <button class="btn btn-sm btn-primary save-role-btn hidden" data-user-id="${user.id}">儲存</button>
                </div>`;
            container.appendChild(userEl);
        });

        container.querySelectorAll('select').forEach(selectEl => {
            selectEl.addEventListener('change', () => {
                container.querySelector(`button.save-role-btn[data-user-id="${selectEl.dataset.userId}"]`).classList.remove('hidden');
            });
        });

        container.querySelectorAll('.save-role-btn').forEach(buttonEl => {
            buttonEl.addEventListener('click', async () => {
                const userId = buttonEl.dataset.userId;
                const newRole = container.querySelector(`select[data-user-id="${userId}"]`).value;
                if (await updateUserRole(userId, newRole)) {
                    alert('角色更新成功！');
                    buttonEl.classList.add('hidden');
                } else {
                    alert('角色更新失敗。');
                }
            });
        });
    }

    async function renderAdminDashboard() {
        const doctorList = document.getElementById('doctor-list');
        if (!doctorList) return;
        doctorList.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
        
        // Fetch doctors and users in parallel
        const [doctors, users] = await Promise.all([getDoctors(), getUsers()]);
        
        doctorList.innerHTML = '';
        doctors.forEach(doc => {
            const item = document.createElement('a');
            item.href = "#";
            item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
            item.innerHTML = `
                <span>${doc.name} - ${doc.specialty}</span>
                <div>
                    <button class="btn btn-sm btn-outline-secondary manage-schedule-btn" data-doctor-id="${doc.id}">排班</button>
                    <button class="btn btn-sm btn-outline-danger delete-doctor-btn" data-doctor-id="${doc.id}">刪除</button>
                </div>`;
            doctorList.appendChild(item);
        });

        // Populate the user dropdown for adding a doctor
        const userSelect = document.getElementById('doctor-user-id');
        if (userSelect) {
            const doctorUserIds = new Set(doctors.map(d => d.userId));
            const usersWithoutDoctorProfile = users.filter(u => !doctorUserIds.has(u.id));
            
            userSelect.innerHTML = '<option value="">請選擇...</option>'; // Reset
            usersWithoutDoctorProfile.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = `${user.username} (${user.email})`;
                userSelect.appendChild(option);
            });
        }

        doctorList.querySelectorAll('.delete-doctor-btn').forEach(button => {
            button.addEventListener('click', async (e) => {
                e.stopPropagation();
                const doctorId = e.target.dataset.doctorId;
                if (confirm('您確定要刪除這位醫師嗎？此操作無法復原。')) {
                    if (await deleteDoctor(doctorId)) {
                        renderAdminDashboard();
                        renderTherapistsPage();
                    } else {
                        alert('刪除失敗');
                    }
                }
            });
        });

        doctorList.querySelectorAll('.manage-schedule-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                const doctorId = e.target.dataset.doctorId;
                const doctorName = e.target.closest('.list-group-item').querySelector('span').textContent.split(' - ')[0];
                state.selectedDoctorId = doctorId;
                
                document.getElementById('schedule-modal-title').textContent = `管理排班 - ${doctorName}`;
                scheduleModal.show();
                renderScheduleEditor(doctorId);
            });
        });
        
        await renderUserManagement();
    }
    
    document.getElementById('add-doctor-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const nameInput = document.getElementById('doctor-name');
        const specialtyInput = document.getElementById('doctor-specialty');
        const userIdSelect = document.getElementById('doctor-user-id');
        
        if (nameInput.value && specialtyInput.value && userIdSelect.value) {
            const newDoctor = await addDoctor({ 
                name: nameInput.value, 
                specialty: specialtyInput.value, 
                userId: parseInt(userIdSelect.value) 
            });
            if (newDoctor) {
                nameInput.value = ''; 
                specialtyInput.value = '';
                userIdSelect.value = '';
                await renderAdminDashboard();
                await renderTherapistsPage();
                alert('醫師新增成功！');
            }
        } else {
            alert('請填寫所有欄位。');
        }
    });

    async function renderDoctorDashboard() {
        const patientListContainer = document.getElementById('patient-list');
        if (!patientListContainer) return;
        patientListContainer.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
        
        const medicalRecords = await getMedicalRecords();
        patientListContainer.innerHTML = '';

        if (!medicalRecords || medicalRecords.length === 0) {
            patientListContainer.innerHTML = '<div class="list-group-item">目前沒有病歷資料。</div>';
            document.getElementById('medical-record-form').classList.add('hidden');
            document.getElementById('record-details-content').querySelector('p').classList.remove('hidden');
            return;
        }

        const patientsMap = new Map();
        medicalRecords.forEach(record => {
            if (record && record.patient && !patientsMap.has(record.patient.id)) {
                patientsMap.set(record.patient.id, record.patient);
            }
        });

        if (patientsMap.size === 0) {
            patientListContainer.innerHTML = '<div class="list-group-item">目前沒有病患資料。</div>';
            return;
        }

        patientsMap.forEach(patient => {
            const item = document.createElement('a');
            item.href = "#";
            item.className = 'list-group-item list-group-item-action';
            item.dataset.patientId = patient.id;
            item.innerHTML = `<h5>${patient.name || '未知病患'}</h5>`;
            patientListContainer.appendChild(item);
        });

        patientListContainer.querySelectorAll('.list-group-item').forEach(item => {
            item.addEventListener('click', async (e) => {
                e.preventDefault();
                patientListContainer.querySelectorAll('.list-group-item').forEach(el => el.classList.remove('active'));
                e.currentTarget.classList.add('active');
                const patientId = e.currentTarget.dataset.patientId;
                await displayMedicalRecordsForPatient(patientId, medicalRecords);
            });
        });
    }

    async function displayMedicalRecordsForPatient(patientId, medicalRecords) {
        document.getElementById('record-details-content').querySelector('p').classList.add('hidden');
        medicalRecordForm.classList.remove('hidden');
        const patientRecords = medicalRecords.filter(mr => mr.patient && mr.patient.id == patientId);
        
        if (patientRecords.length > 0) {
            const latestRecord = patientRecords.sort((a, b) => new Date(b.recordDate) - new Date(a.recordDate))[0];
            populateMedicalRecordForm(latestRecord);
        } else {
            clearMedicalRecordForm();
            medicalRecordPatientIdInput.value = patientId;
        }
    }

    function clearMedicalRecordForm() {
        medicalRecordIdInput.value = '';
        medicalRecordPatientIdInput.value = '';
        recordDiagnosisInput.value = '';
        recordTreatmentInput.value = '';
        recordNotesInput.value = '';
    }

    function populateMedicalRecordForm(record) {
        medicalRecordIdInput.value = record.id;
        medicalRecordPatientIdInput.value = record.patientId;
        recordDiagnosisInput.value = record.diagnosis;
        recordTreatmentInput.value = record.treatment;
        recordNotesInput.value = record.notes;
    }

    newMedicalRecordBtn.addEventListener('click', () => {
        const currentPatientId = medicalRecordPatientIdInput.value;
        clearMedicalRecordForm();
        medicalRecordPatientIdInput.value = currentPatientId;
    });

    function renderScheduleEditor(doctorId) {
        const calendarGrid = document.getElementById('schedule-calendar-grid');
        const monthYearDisplay = document.getElementById('schedule-month-year');
        const prevMonthBtn = document.getElementById('schedule-prev-month');
        const nextMonthBtn = document.getElementById('schedule-next-month');

        const renderCalendar = () => {
            calendarGrid.innerHTML = '';
            const date = state.scheduleCalendarDate;
            const year = date.getFullYear();
            const month = date.getMonth();
            monthYearDisplay.textContent = `${year}年 ${month + 1}月`;
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const dayNames = ['日', '一', '二', '三', '四', '五', '六'];
            dayNames.forEach(name => calendarGrid.innerHTML += `<div class="day-name">${name}</div>`);
            for (let i = 0; i < firstDay; i++) calendarGrid.innerHTML += '<div class="day empty"></div>';
            for (let i = 1; i <= daysInMonth; i++) {
                const dayEl = document.createElement('div');
                dayEl.className = 'day';
                dayEl.textContent = i;
                dayEl.dataset.date = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                dayEl.addEventListener('click', (e) => {
                    const selectedDate = e.target.dataset.date;
                    document.getElementById('selected-date-display').textContent = selectedDate;
                    calendarGrid.querySelectorAll('.day').forEach(d => d.classList.remove('selected'));
                    e.target.classList.add('selected');
                    renderTimeSlots(state.selectedDoctorId, selectedDate);
                });
                calendarGrid.appendChild(dayEl);
            }
        };
        prevMonthBtn.onclick = () => { state.scheduleCalendarDate.setMonth(state.scheduleCalendarDate.getMonth() - 1); renderCalendar(); };
        nextMonthBtn.onclick = () => { state.scheduleCalendarDate.setMonth(state.scheduleCalendarDate.getMonth() + 1); renderCalendar(); };
        renderCalendar();
    }

    async function renderTimeSlots(doctorId, date) {
        const timeSlotsList = document.getElementById('time-slots-list');
        timeSlotsList.innerHTML = '<div class="list-group-item">Loading...</div>';
        try {
            const response = await fetchWithAuth(`${API_BASE_URL}/doctors/${doctorId}/availability?date=${date}`);
            if (!response.ok) throw new Error('Failed to fetch time slots');
            const timeSlots = await response.json();
            timeSlotsList.innerHTML = '';
            if (timeSlots.length === 0) {
                timeSlotsList.innerHTML = '<div class="list-group-item">這天沒有排班。</div>';
                return;
            }
            timeSlots.forEach(slot => {
                const startTime = new Date(slot.startTime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                const endTime = new Date(slot.endTime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                const slotEl = document.createElement('div');
                slotEl.className = 'list-group-item d-flex justify-content-between align-items-center';
                slotEl.innerHTML = `<span>${startTime} - ${endTime}</span> <button class="btn btn-sm btn-outline-danger delete-availability-btn" data-id="${slot.id}">刪除</button>`;
                timeSlotsList.appendChild(slotEl);
            });
        } catch (error) {
            timeSlotsList.innerHTML = `<div class="list-group-item text-danger">${error.message}</div>`;
        }
    }

    function renderMySchedulePage() {
        const calendarGrid = document.getElementById('my-schedule-calendar-grid');
        const monthYearDisplay = document.getElementById('my-schedule-month-year');
        const prevMonthBtn = document.getElementById('my-schedule-prev-month');
        const nextMonthBtn = document.getElementById('my-schedule-next-month');
        const selectedDateDisplay = document.getElementById('my-schedule-selected-date-display');
        const detailsContent = document.getElementById('my-schedule-details-content');
        const cancellationHoursInput = document.getElementById('cancellation-hours-input');

        // Fetch and display current settings
        fetchWithAuth(`${API_BASE_URL}/doctor_settings.php`)
            .then(response => response.json())
            .then(settings => {
                cancellationHoursInput.value = settings.cancellationPolicyHours;
            })
            .catch(error => console.error('Error fetching doctor settings:', error));

        const renderCalendar = async () => {
            if (!state.user || !state.user.doctorId) return;
            const response = await fetchWithAuth(`${API_BASE_URL}/doctors/${state.user.doctorId}/availability`);
            const allAvailabilities = await response.json();
            const scheduleMap = new Map();
            if (Array.isArray(allAvailabilities)) {
                allAvailabilities.forEach(avail => {
                    const dateStr = avail.startTime.substring(0, 10);
                    if (!scheduleMap.has(dateStr)) scheduleMap.set(dateStr, []);
                    scheduleMap.get(dateStr).push(`${avail.startTime.substring(11, 16)}-${avail.endTime.substring(11, 16)}`);
                });
            }
            calendarGrid.innerHTML = '';
            const date = state.myScheduleCalendarDate;
            const year = date.getFullYear();
            const month = date.getMonth();
            monthYearDisplay.textContent = `${year}年 ${month + 1}月`;
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const dayNames = ['日', '一', '二', '三', '四', '五', '六'];
            dayNames.forEach(name => calendarGrid.innerHTML += `<div class="day-name">${name}</div>`);
            for (let i = 0; i < firstDay; i++) calendarGrid.innerHTML += '<div class="day empty"></div>';
            for (let i = 1; i <= daysInMonth; i++) {
                const dayEl = document.createElement('div');
                dayEl.className = 'day';
                const fullDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                dayEl.dataset.date = fullDate;
                dayEl.innerHTML = `<div class="day-content">${i}</div>`;
                if (scheduleMap.has(fullDate)) {
                    dayEl.classList.add('has-schedule');
                    dayEl.innerHTML += `<div class="schedule-info">${scheduleMap.get(fullDate).join('<br>')}</div>`;
                }
                dayEl.addEventListener('click', (e) => {
                    const selectedDate = e.currentTarget.dataset.date;
                    selectedDateDisplay.textContent = selectedDate;
                    calendarGrid.querySelectorAll('.day').forEach(d => d.classList.remove('selected'));
                    e.currentTarget.classList.add('selected');
                    renderDoctorAvailability(state.user.doctorId, selectedDate);
                });
                calendarGrid.appendChild(dayEl);
            }
        };
        prevMonthBtn.onclick = () => { state.myScheduleCalendarDate.setMonth(state.myScheduleCalendarDate.getMonth() - 1); renderCalendar(); };
        nextMonthBtn.onclick = () => { state.myScheduleCalendarDate.setMonth(state.myScheduleCalendarDate.getMonth() + 1); renderCalendar(); };
        renderCalendar();
    }

    document.getElementById('save-settings-btn')?.addEventListener('click', async () => {
        const hours = document.getElementById('cancellation-hours-input').value;
        if (hours === '' || parseInt(hours) < 0) {
            alert('請輸入有效的時間（必須大於或等於0）。');
            return;
        }
        try {
            const response = await fetchWithAuth(`${API_BASE_URL}/doctor_settings.php`, {
                method: 'PUT',
                body: JSON.stringify({ cancellationPolicyHours: parseInt(hours) })
            });
            if (response.ok) {
                alert('設定已成功儲存！');
            } else {
                alert(`儲存失敗: ${await response.text()}`);
            }
        } catch (error) {
            alert(`發生錯誤：${error.message}`);
        }
    });

    async function renderDoctorAvailability(doctorId, date) {
        const detailsContent = document.getElementById('my-schedule-details-content');
        detailsContent.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
        try {
            // Fetch both availability and appointments in parallel
            const [availResponse, apptResponse] = await Promise.all([fetchWithAuth(`${API_BASE_URL}/availability.php?doctorId=${doctorId}&date=${date}`),
                fetchWithAuth(`${API_BASE_URL}/get_doctor_appointments.php?date=${date}`)
            ]);

            if (!availResponse.ok) throw new Error(`Error fetching availability: ${availResponse.statusText}`);
            if (!apptResponse.ok) throw new Error(`Error fetching appointments: ${apptResponse.statusText}`);

            const availability = await availResponse.json();
            const appointments = await apptResponse.json();

            let content = `<h4 class="h5">${date} 的排班與預約</h4>`;

            if (availability.length === 0 && appointments.length === 0) {
                content += '<p class="text-muted">這天沒有排班或預約。</p>';
                detailsContent.innerHTML = content;
                return;
            }

            // Create a map of appointments for quick lookup
            const appointmentMap = new Map(appointments.map(a => [new Date(a.appointmentTime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }), a.patientName]));

            content += '<ul class="list-group">';
            availability.forEach(slot => {
                const startTime = new Date(slot.startTime);
                const endTime = new Date(slot.endTime);
                
                // Iterate through the availability slot in 30-minute increments
                let currentTime = startTime;
                while (currentTime < endTime) {
                    const timeString = currentTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    const patientName = appointmentMap.get(timeString);

                    if (patientName) {
                        // This slot is booked
                        content += `<li class="list-group-item list-group-item-action list-group-item-primary d-flex justify-content-between align-items-center">
                                        <span>${timeString} - ${currentTime.addMinutes(30).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                                        <span class="fw-bold">${patientName}</span>
                                    </li>`;
                    } else {
                        // This slot is available
                         content += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>${timeString} - ${currentTime.addMinutes(30).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                                        <span class="text-success">可預約</span>
                                     </li>`;
                    }
                    currentTime = currentTime.addMinutes(30);
                }
            });
             content += '</ul>';

            detailsContent.innerHTML = content;
        } catch (error) {
            detailsContent.innerHTML = `<p class="text-danger">讀取排班時發生錯誤: ${error.message}</p>`;
        }
    }

    document.getElementById('add-time-range-btn')?.addEventListener('click', async () => {
        const selectedDate = document.getElementById('my-schedule-selected-date-display').textContent;
        if (!selectedDate) { alert('請先選擇日期。'); return; }
        const startTime = document.getElementById('new-start-time-input').value;
        const endTime = document.getElementById('new-end-time-input').value;
        const doctorId = state.user.doctorId;
        if (!doctorId) { alert('錯誤：找不到醫師ID，請重新登入再試。'); return; }
        if (!startTime || !endTime) { alert('請輸入開始時間和結束時間。'); return; }
        if (startTime >= endTime) { alert('結束時間必須晚於開始時間。'); return; }
        try {
            const availabilityData = {
                doctorId: parseInt(doctorId),
                startTime: `${selectedDate}T${startTime}:00`,
                endTime: `${selectedDate}T${endTime}:00`
            };
            const response = await fetchWithAuth(`${API_BASE_URL}/availability.php?doctorId=${doctorId}`, {
                method: 'POST',
                body: JSON.stringify(availabilityData)
            });
            if (response.ok) {
                document.getElementById('new-start-time-input').value = '';
                document.getElementById('new-end-time-input').value = '';
                renderMySchedulePage(); // Re-render whole calendar page
                renderDoctorAvailability(doctorId, selectedDate);
                alert('排班時段新增成功！');
            } else {
                alert(`新增時段失敗: ${await response.text()}`);
            }
        } catch (error) { alert(`發生錯誤：${error.message}`); }
    });

    document.getElementById('my-schedule-details-content')?.addEventListener('click', async (e) => {
        if (e.target.classList.contains('delete-availability-btn')) {
            const availabilityId = e.target.dataset.id;
            const doctorId = state.user.doctorId;
            if (!doctorId) { alert('錯誤：找不到醫師ID。'); return; }
            if (confirm('您確定要刪除這個時段嗎？')) {
                try {
                    const response = await fetchWithAuth(`${API_BASE_URL}/availability.php?id=${availabilityId}`, { method: 'DELETE' });
                    if (response.ok) {
                        const selectedDate = document.getElementById('my-schedule-selected-date-display').textContent;
                        renderMySchedulePage(); // Re-render whole calendar page
                        renderDoctorAvailability(doctorId, selectedDate);
                    } else {
                        alert(`刪除時段失敗: ${await response.text()}`);
                    }
                } catch (error) { alert(`發生錯誤：${error.message}`); }
            }
        }
    });

    // --- INITIALIZATION ---
    function initializeGoogleSignIn() {
        if (typeof google === 'undefined' || !google.accounts || !google.accounts.id) {
            setTimeout(initializeGoogleSignIn, 500);
            return;
        }
        try {
            google.accounts.id.initialize({ client_id: GOOGLE_CLIENT_ID, callback: handleGoogleCredentialResponse });
            google.accounts.id.renderButton(document.getElementById('gsi-container'), { theme: 'outline', size: 'large', width: '280' });
        } catch (e) { console.error('Google Sign-In initialization failed', e); }
    }

    async function initializeApp() {
        const token = localStorage.getItem('jwt_token');
        if (token) {
            saveToken(token);
            await fetchDoctorInfo();
        }
        changeLanguage(languageSelect.value);
        initializeGoogleSignIn();
    }

    initializeApp();

    // --- DYNAMIC EFFECTS (PORSCHE STYLE) ---

    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.remove('navbar-transparent');
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.add('navbar-transparent');
                navbar.classList.remove('navbar-scrolled');
            }
        });
    }

    // Fade-in sections on scroll
    const sectionsToFade = document.querySelectorAll('.fade-in-section');
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.15 // Trigger when 15% of the element is visible
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target); // Animate only once
            }
        });
    }, observerOptions);

    sectionsToFade.forEach(section => {
        observer.observe(section);
    });
});