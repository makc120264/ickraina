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
    <script src="{{ asset('js/orders.js') }}"></script>
@endsection
