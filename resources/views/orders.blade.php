@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <h1 class="h3">Список замовлень</h1>
            <div id="messages" class="my-3" aria-live="polite"></div>
            <div class="card">
                <div class="card-body">
                    <div id="orders-empty" class="text-muted">Завантаження...</div>
                    <ul id="orders-list" class="list-group list-group-flush d-none"></ul>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mt-4 mt-lg-0">
            <h1 class="h3">Створити замовлення</h1>
            <div class="my-3"></div>
            <div class="card">
                <div class="card-body">
                    <form id="create-order-form" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label">Ім'я</label>
                            <input type="text" class="form-control" id="name" name="name" maxlength="255" required>
                            <div class="invalid-feedback">Вкажіть ім'я (1-255 символів)</div>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Ціна</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="price" name="price"
                                   required>
                            <div class="invalid-feedback">Вкажіть коректну ціну (0 або більше)</div>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Статус</label>
                            <select id="status" name="status" class="form-select">
                                <option value="new">new</option>
                                <option value="processing">processing</option>
                                <option value="completed">completed</option>
                                <option value="cancelled">cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Створити</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration for JS -->
    <meta name="api-key" content="{{ env('API_KEY', 'secret123') }}">
    <script>
        (function () {
            const API_BASE = '/api';
            const API_KEY = document.querySelector('meta[name="api-key"]').getAttribute('content');

            const els = {
                ordersList: document.getElementById('orders-list'),
                ordersEmpty: document.getElementById('orders-empty'),
                form: document.getElementById('create-order-form'),
                messages: document.getElementById('messages'),
                name: document.getElementById('name'),
                price: document.getElementById('price'),
                status: document.getElementById('status')
            };

            function showMessage(type, text) {
                // type: 'success' | 'danger' | 'info' | 'warning'
                els.messages.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                                ${text}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                               </div>`;
            }

            function renderOrders(items) {
                if (!items || items.length === 0) {
                    els.ordersList.classList.add('d-none');
                    els.ordersEmpty.textContent = 'Замовлення відсутні';
                    els.ordersEmpty.classList.remove('d-none');
                    return;
                }

                els.ordersEmpty.classList.add('d-none');
                els.ordersList.classList.remove('d-none');
                els.ordersList.innerHTML = '';

                items.forEach(o => {
                    let status = '';
                    const li = document.createElement('li');
                    li.className = 'list-group-item d-flex justify-content-between align-items-center';
                    status = getStatusClass(o.status);
                    li.innerHTML = `<div>
                                      <div><strong>#${o.id}</strong> — ${o.name ? o.name : '(без імені)'} — статус: <span class="badge text-bg-${status}">${o.status}</span></div>
                                      <div class="text-muted small">Створено: ${new Date(o.created_at).toLocaleString()}</div>
                                    </div>
                                    <div class="fw-semibold">${(o.price ?? 0).toFixed(2)} грн</div>
                                  `;
                    els.ordersList.appendChild(li);
                });
            }

            function getStatusClass(oStatus) {
                let status = '';
                switch (oStatus) {
                    case 'completed':
                        status = 'success';
                        break;
                    case 'new':
                        status = 'primary';
                        break;
                    case 'processing':
                        status = 'info';
                        break;
                    case 'cancelled':
                        status = 'danger';
                        break;
                }

                return status;
            }

            async function loadOrders() {
                try {
                    const res = await fetch(`${API_BASE}/orders?paginate=false`, {
                        headers: {'X-API-KEY': API_KEY}
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        const msg = (data && data.message) ? data.message : 'Ошибка загрузки списка заказов';
                        showMessage('danger', msg);
                        return;
                    }
                    // data.payload.items (without pagination)
                    const items = data && data.payload && data.payload.items ? data.payload.items : [];
                    renderOrders(items);
                } catch (e) {
                    showMessage('danger', 'Мережева помилка під час завантаження замовлень');
                }
            }

            async function createOrder(payload) {
                const res = await fetch(`${API_BASE}/orders`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-API-KEY': API_KEY
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json().catch(() => ({}));
                return {res, data};
            }

            els.form.addEventListener('submit', async function (ev) {
                ev.preventDefault();

                // simple validation
                const nameVal = (els.name.value || '').trim();
                if (nameVal.length === 0 || nameVal.length > 255) {
                    els.name.classList.add('is-invalid');
                    return;
                } else {
                    els.name.classList.remove('is-invalid');
                }

                const priceVal = parseFloat(els.price.value);
                if (isNaN(priceVal) || priceVal < 0) {
                    els.price.classList.add('is-invalid');
                    return;
                } else {
                    els.price.classList.remove('is-invalid');
                }

                const payload = {
                    name: nameVal,
                    price: priceVal,
                    status: els.status.value
                };

                try {
                    const {res, data} = await createOrder(payload);
                    if (res.ok) {
                        showMessage('success', data.message || 'Замовлення створено');
                        els.form.reset();
                        await loadOrders();
                    } else {
                        const errorDetails = data && data.payload && data.payload.errors ? data.payload.errors : null;
                        let msg = data && data.message ? data.message : 'Помилка створення замовлення';
                        if (errorDetails) {
                            const list = Object.entries(errorDetails).map(([k, v]) => `${k}: ${v}`).join('; ');
                            msg += `. ${list}`;
                        }
                        showMessage('danger', msg);
                    }
                } catch (e) {
                    showMessage('danger', 'Мережева помилка під час створення замовлення');
                }
            });

            // initial loading
            document.addEventListener('DOMContentLoaded', loadOrders());
        })();
    </script>
@endsection
