@extends('layouts.app')

@section('title', 'Concurso')

@section('content')
    <div class="row mx-3 pt-3">
        <div class="col-xs-12 col-md-6">
            <div class="card shadow-sm" style="height: 100%;">
                <div class="card-body">
                    <h5 class="card-title text-center mb-4">Registrarse</h5>

                    <form id="registerForm">
                        @csrf

                        <div class="row">
                            <div class="col-xs-12 col-md-6 mb-3">
                                <label for="name" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="name" name="name">
                                <div class="text-danger fs-10 hidden" id="error-name"></div>
                            </div>

                            <div class="col-xs-12 col-md-6 mb-3">
                                <label for="lastName" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="lastName" name="lastName">
                                <div class="text-danger fs-10 hidden" id="error-lastName"></div>
                            </div>

                            <div class="col-xs-12 col-md-6 mb-3">
                                <label for="identification" class="form-label">Cédula</label>
                                <input type="number" class="form-control" id="identification" name="identification">
                                <div class="text-danger fs-10 hidden" id="error-identification"></div>
                            </div>

                            <div class="col-xs-12 col-md-6 mb-3">
                                <label for="phone" class="form-label">Teléfono</label>
                                <input type="number" class="form-control" id="phone" name="phone">
                                <div class="text-danger fs-10 hidden" id="error-phone"></div>
                            </div>

                            <div class="col-xs-12 col-md-6 mb-3">
                                <label for="department_id" class="form-label">Departamento</label>
                                <select class="form-select" id="department_id" name="department_id">
                                    <option value="">Seleccione un departamento</option>
                                </select>
                                <div class="text-danger fs-10 hidden" id="error-department_id"></div>
                            </div>

                            <div class="col-xs-12 col-md-6 mb-3">
                                <label for="city_id" class="form-label">Ciudad</label>
                                <select class="form-select" id="city_id" name="city_id" disabled>
                                    <option value="">Seleccione una ciudad</option>
                                </select>
                                <div class="text-danger fs-10 hidden" id="error-city_id"></div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="text" class="form-control" id="email" name="email">
                                <div class="text-danger fs-10 hidden" id="error-email"></div>
                            </div>

                            <div class="col-md-12 mb-3 ms-3 form-check">
                                <input type="checkbox" class="form-check-input" id="authorization" name="authorization">
                                <label class="form-check-label" for="authorization">
                                    Autorizo el tratamiento de mis datos de acuerdo con la
                                    finalidad establecida en la política de protección de datos personales
                                </label>
                                <div class="text-danger fs-10 hidden" id="error-authorization"></div>
                            </div>

                            <div class="d-flex justify-content-center">
                                <button type="submit" class="btn btn-success w-50">
                                    <i class="mdi mdi-plus"></i>
                                    Registrarse
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-4 shadow-sm" style="height: 100%;">
                <div class="card-body">
                    <h5 class="card-title text-center mb-4">Ruleta</h5>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-center">
                                <div id="wheel-container" class="text-center mb-3 pRelative">
                                    <canvas id="wheel" width="300" height="300"></canvas>
                                    <div class="abs">
                                        <= </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="text-center">
                                        <button id="spinButton" class="btn btn-success" disabled>
                                            <i class="mdi mdi-reload"></i>
                                            Girar Ruleta
                                        </button>
                                        <button class="btn btn-primary" id="exportBtn">
                                            <i class="mdi mdi-file-excel"></i>
                                            Descargar Reporte
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endsection

        @push('scripts')
            <script>
                $(document).ready(function() {
                    let listDepartaments = [];
                    let listCities = [];

                    const verificationEmail = (correo) => {
                        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        return regex.test(correo);
                    }

                    $.ajax({
                        url: '/departments',
                        method: 'GET',
                        success: function(response) {
                            response.forEach(dep => {
                                $('#department_id').append(
                                    `<option value="${dep.id}">${dep.name}</option>`);
                            });

                            listDepartaments = response;
                        },
                        error: async function() {
                            await alertToast({
                                text: xhr?.responseJSON?.message ??
                                    "Hubo un error inesperado",
                                icon: 'error'
                            });
                        }
                    });

                    $('#department_id').change(function() {
                        const departmentId = $(this).val();
                        $('#city_id').empty().append('<option value="">Seleccione una ciudad</option>');

                        if (departmentId) {
                            $('#city_id').prop('disabled', false);
                            $.ajax({
                                url: '/cities',
                                method: 'GET',
                                data: {
                                    department_id: departmentId
                                },
                                success: async function(response) {
                                    response.forEach(city => {
                                        $('#city_id').append(
                                            `<option value="${city.id}">${city.name}</option>`
                                        );
                                    });
                                },
                                error: async function() {
                                    await alertToast({
                                        text: xhr?.responseJSON?.message ??
                                            "Hubo un error inesperado",
                                        icon: 'error'
                                    });
                                }
                            });
                        } else {
                            $('#city_id').prop('disabled', true);
                        }
                    });

                    $('#registerForm').submit(function(event) {
                        event.preventDefault();
                        $('.text-danger').text('').addClass('hidden');

                        const name = $('#name').val();
                        const lastName = $('#lastName').val();
                        const identification = $('#identification').val();
                        const department_id = $('#department_id').val();
                        const city_id = $('#city_id').val();
                        const phone = $('#phone').val();
                        const authorization = $('#authorization').is(':checked') ? 1 : 0;
                        const email = $('#email').val();
                        let hasError = false;

                        if (!name) {
                            $('#error-name').text('Este campo es obligatorio').removeClass('hidden');
                            hasError = true;
                        }
                        if (!lastName) {
                            $('#error-lastName').text('Este campo es obligatorio').removeClass('hidden');
                            hasError = true;
                        }
                        if (!identification) {
                            $('#error-identification').text('Este campo es obligatorio').removeClass('hidden');
                            hasError = true;
                        }
                        if (!department_id) {
                            $('#error-department_id').text('Este campo es obligatorio').removeClass('hidden');
                            hasError = true;
                        }
                        if (!city_id) {
                            $('#error-city_id').text('Este campo es obligatorio').removeClass('hidden');
                            hasError = true;
                        }
                        if (!phone) {
                            $('#error-phone').text('Este campo es obligatorio').removeClass('hidden');
                            hasError = true;
                        }
                        if (!authorization) {
                            $('#error-authorization').text('Este campo es obligatorio').removeClass('hidden');
                            hasError = true;
                        }
                        if (!email) {
                            $('#error-email').text('Este campo es obligatorio').removeClass('hidden');
                            hasError = true;
                        } else if (!verificationEmail(email)) {
                            $('#error-email').text('Debe ser un correo válido.').removeClass('hidden');
                            hasError = true;
                        }

                        if (hasError) return;

                        $.ajax({
                            url: "{{ route('register') }}",
                            method: "POST",
                            data: {
                                name,
                                lastName,
                                identification,
                                department_id,
                                city_id,
                                phone,
                                authorization,
                                email,
                                _token: '{{ csrf_token() }}'
                            },
                            success: async function(response) {
                                await alertToast({
                                    text: response.message,
                                    icon: 'success'
                                });
                                document.location = '/';
                            },
                            error: async function(xhr) {
                                await alertToast({
                                    text: xhr?.responseJSON?.message ??
                                        "Hubo un error inesperado",
                                    icon: 'error'
                                });
                            }
                        });
                    });

                    let users = [];

                    function drawWheel(names) {
                        const canvas = document.getElementById('wheel');
                        const ctx = canvas.getContext('2d');
                        const numSegments = names.length;
                        const angle = 2 * Math.PI / numSegments;

                        ctx.clearRect(0, 0, canvas.width, canvas.height);

                        names.forEach((name, i) => {
                            const startAngle = i * angle;
                            const endAngle = startAngle + angle;

                            ctx.beginPath();
                            ctx.moveTo(150, 150);
                            ctx.arc(150, 150, 150, startAngle, endAngle);
                            ctx.fillStyle = `hsl(${i * (360 / numSegments)}, 70%, 70%)`;
                            ctx.fill();
                            ctx.stroke();

                            ctx.save();
                            ctx.translate(150, 150);
                            ctx.rotate(startAngle + angle / 2);
                            ctx.textAlign = "right";
                            ctx.fillStyle = "black";
                            ctx.font = "bold 14px sans-serif";
                            ctx.fillText(name.name, 140, 5);
                            ctx.restore();
                        });
                    }

                    function loadUsers() {
                        $.ajax({
                            url: '/users',
                            method: 'GET',
                            success: function(response) {
                                if (response.length >= 5) {
                                    users = response;
                                    drawWheel(users);
                                    $('#spinButton').prop('disabled', false);

                                    $('#wheel-container').show();
                                    $('#not-enough-users-message').hide();
                                } else {
                                    $('#spinButton').prop('disabled', true);
                                    $('#wheel-container').hide();

                                    if ($('#not-enough-users-message').length === 0) {
                                        const usersNeeded = 5 - response.length;
                                        const message = `<div id="not-enough-users-message" class="alert alert-warning text-center">
                                                <h5>Se necesitan más participantes</h5>
                                                <p>Para iniciar el sorteo, se requieren al menos 5 participantes.</p>
                                                <p>Faltan ${usersNeeded} participante${usersNeeded !== 1 ? 's' : ''} para poder iniciar.</p>
                                            </div>`;

                                        $('#wheel-container').after(message);
                                    } else {
                                        const usersNeeded = 5 - response.length;
                                        $('#not-enough-users-message p:last-child').text(
                                            `Faltan ${usersNeeded} participante${usersNeeded !== 1 ? 's' : ''} para poder iniciar.`
                                        );
                                    }
                                }
                            },
                            error: function() {
                                console.error('Error al cargar usuarios');
                                $('#wheel-container').hide();
                                if ($('#not-enough-users-message').length === 0) {
                                    const message = `<div id="not-enough-users-message" class="alert alert-danger text-center">
                                            <h5>Error</h5>
                                            <p>No se pudieron cargar los participantes. Intente nuevamente más tarde.</p>
                                        </div>`;
                                    $('#wheel-container').after(message);
                                }
                            }
                        });
                    }

                    let currentRotation = 0;
                    $('#spinButton').click(function() {
                        const canvas = document.getElementById('wheel');
                        const ctx = canvas.getContext('2d');

                        const randomRotation = Math.random() * 360 + 3600; // mínimo 10 vueltas
                        const duration = 4000; // 4 segundos
                        const start = performance.now();

                        function animate(now) {
                            const elapsed = now - start;
                            const progress = Math.min(elapsed / duration, 1);
                            const easeProgress = 1 - Math.pow(1 - progress, 3); // Ease out cubic
                            const angle = currentRotation + randomRotation * easeProgress;

                            ctx.clearRect(0, 0, canvas.width, canvas.height);
                            ctx.save();
                            ctx.translate(150, 150);
                            ctx.rotate((angle * Math.PI) / 180);
                            ctx.translate(-150, -150);

                            drawWheel(users);

                            ctx.restore();

                            if (progress < 1) {
                                requestAnimationFrame(animate);
                            } else {
                                currentRotation = (currentRotation + randomRotation) % 360;

                                const winningIndex = Math.floor(users.length - (currentRotation / 360 * users
                                    .length)) % users.length;
                                const winner = users[winningIndex];

                                if (winner)
                                    $.ajax({
                                        url: "{{ route('updateWin') }}",
                                        method: "POST",
                                        data: {
                                            id: winner.id,
                                            _token: '{{ csrf_token() }}'
                                        },
                                        success: async function(response) {
                                            await alertToast({
                                                text: `¡Felicidades ${winner.name}! 🎉`,
                                                icon: 'success'
                                            });
                                            document.location = '/';
                                        },
                                        error: async function(xhr) {
                                            await alertToast({
                                                text: xhr?.responseJSON?.message ??
                                                    "Hubo un error inesperado",
                                                icon: 'error'
                                            });
                                        }
                                    });
                            }
                        }

                        requestAnimationFrame(animate);
                    });

                    document.getElementById('exportBtn').addEventListener('click', async function() {

                        $('#exportBtn').prop('disabled', true);
                        await alertToast({
                            text: "Por favor espere, mientras generamos el archivo",
                            icon: 'info',
                            timer: 15000
                        });

                        $.ajax({
                            url: "{{ route('listUsers') }}",
                            method: "GET",
                            success: async function(response) {
                                // Usamos Promise.all para esperar a que todas las promesas se resuelvan
                                let formatRes = await Promise.all(response.map(async (u) => {
                                    const departamento = listDepartaments.find(d =>
                                            u
                                            .department_id == d.id)?.name ??
                                        "No Found";

                                    let jsonData = {
                                        "Nombre": u.name,
                                        "Apellido": u.lastName,
                                        "Cédula": u.identification,
                                        "Departamento": departamento,
                                        "Ciudad": u.city_id,
                                        "Celular": u.phone,
                                        "Correo Electrónico": u.email,
                                        "Autorización": u.authorization ? 1 : 0,
                                        "Fecha y Hora registro": moment(u
                                            .created_at).format(
                                            'YYYY-MM-DD HH:mm:ss'),
                                        "Veces Ganadas": u.win,
                                    };

                                    try {
                                        let cityData = await $.ajax({
                                            url: '/cities',
                                            method: 'GET',
                                            data: {
                                                department_id: u
                                                    .department_id
                                            }
                                        });

                                        jsonData['Ciudad'] = cityData.find(d => u
                                                .city_id == d.id)
                                            ?.name ?? "No Found";

                                    } catch (error) {
                                        console.error("Error al obtener ciudad:",
                                            error);
                                    }

                                    return jsonData;
                                }));

                                const wb = XLSX.utils.book_new();
                                const ws = XLSX.utils.json_to_sheet(formatRes);
                                XLSX.utils.book_append_sheet(wb, ws, "Datos");
                                XLSX.writeFile(wb,
                                    `reporte_${moment().format('YYYY-MM-DD HH:mm:ss')}.xlsx`
                                );
                                $('#exportBtn').prop('disabled', false);
                            },
                            error: async function(xhr) {
                                await alertToast({
                                    text: xhr?.responseJSON?.message ??
                                        "Hubo un error inesperado",
                                    icon: 'error'
                                });
                            }
                        });
                    });

                    loadUsers();
                });
            </script>
        @endpush
