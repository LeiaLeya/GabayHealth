@extends('layouts.app')
@section('content')
@php($hideSidebar = true)
<style>
    html, body {
        height: 100%;
        width: 100%;
        margin: 0;
        padding: 0;
        background: #fff !important;
        overflow-x: hidden !important;
        overflow-y: hidden;
        box-sizing: border-box;
    }
    .container-fluid {
        min-height: 100vh;
        width: 100%;
        background: #fff !important;
        padding: 0;
        margin: 0;
        overflow: hidden !important;
    }
    .registration-form .form-control, .registration-form .form-select {
        min-height: 44px;
        font-size: 14px;
        border-radius: 0.75rem;
        padding: 0.5rem 1rem;
        width: 100%;
        min-width: 0;
    }
</style>
<div class="container-fluid d-flex align-items-center justify-content-center" style="height: 100vh; width: 100%; background: #fff; overflow: hidden;">
    <div class="row w-100 align-items-center justify-content-center" style="max-width: 1100px; min-height: 540px; overflow: hidden; background: transparent;">
        <div class="col-md-5 d-flex flex-column align-items-center justify-content-center" style="height: 520px;">
            <div style="background: #2563eb; border-radius: 2rem; max-width: 450px; width: 100%; max-height: 700px; height: 600px; display: flex; flex-direction: column; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.03); margin-right: -80px;">
                <img src='{{ asset('images/gabayhealth_logo.png') }}' alt='GabayHealth Logo' style='width: 60px; height: 60px; margin-bottom: 10px;'>
                <h3 style="color: #fff; font-weight: 700; text-align: center; margin-bottom: -0.1rem; font-size:2rem;">Welcome to GabayHealth</h3>
                <p style="color: #e0e7ff; text-align: center; font-size: 14px;">Register your Barangay Health Center to get started.</p>
            </div>
        </div>

        <div class="col-md-7 d-flex align-items-center justify-content-center" style="height: 480px;">
            <div class="w-100" style="max-width: 540px;">
                <h2 class="fw-bold mb-1" style="font-size: 2rem; color: #222;">Register Barangay Health Center</h2>
                <div class="mb-2" style="color: #666; font-size: 1.08rem;">Let’s get you all set up so you can access your account.</div>
                @if(session('success'))
                    <div class="alert alert-success py-2 px-3 mb-2" style="font-size:0.95rem;">{{ session('success') }}</div>
                @endif
                <form class="registration-form" method="POST" action="{{ route('register.bhw.submit') }}">
                    @csrf
                    <div class="row g-2">
                        <div class="col-6 mb-2">
                            <input type="text" name="username" class="form-control form-control-sm" placeholder="Username" required>
                        </div>
                        <div class="col-6 mb-2">
                            <input type="text" name="healthCenterName" class="form-control form-control-sm" placeholder="Health Center Name" required>
                        </div>
                        <div class="col-6 mb-2">
                            <input type="password" name="password" class="form-control form-control-sm" placeholder="Password" required>
                        </div>
                        <div class="col-6 mb-2">
                            <input type="password" name="password_confirmation" class="form-control form-control-sm" placeholder="Confirm Password" required>
                        </div>
                        <div class="col-12 mb-2">
                            <input type="text" name="fullAddress" class="form-control form-control-sm" placeholder="Full Address" required>
                        </div>
                        <div class="col-6 mb-2">
                            <select id="region" name="region" class="form-select form-select-sm" required>
                                <option value="">Region</option>
                            </select>
                        </div>
                        <div class="col-6 mb-2">
                            <select id="province" name="province" class="form-select form-select-sm" required>
                                <option value="">Province</option>
                            </select>
                        </div>
                        <div class="col-6 mb-2">
                            <select id="city" name="city" class="form-select form-select-sm" required>
                                <option value="">City/Municipality</option>
                            </select>
                        </div>
                        <div class="col-6 mb-2">
                            <select id="barangay" name="barangay" class="form-select form-select-sm" required>
                                <option value="">Barangay</option>
                            </select>
                        </div>
                        <div class="col-6 mb-2">
                            <input type="text" name="postalCode" class="form-control form-control-sm" placeholder="Postal Code" required>
                        </div>
                        <div class="col-6 mb-2">
                            <select name="rhuId" class="form-select form-select-sm" required>
                                <option value="">Parent RHU</option>
                                @foreach($rhus as $rhu)
                                    <option value="{{ $rhu['id'] }}">{{ $rhu['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-check mt-2 mb-2" style="font-size:0.97rem;">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="#" style="color:#2563eb;">Terms</a> and <a href="#" style="color:#2563eb;">Privacy Policies</a>
                        </label>
                    </div>
                    <button type="submit" class="btn w-100" style="background:#2563eb; color:#fff; font-weight:600; font-size:1.08rem;">Register</button>
                    <div class="text-center mt-2">
                        <span style="color:#2563eb; font-weight:500;">Already have an account?</span>
                        <a href="{{ route('login') }}" style="color:#1d4ed8; font-weight:600; text-decoration:underline;">Login here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const regionSelect = document.getElementById('region');
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const barangaySelect = document.getElementById('barangay');

    function resetSelect(select, placeholder) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
    }

    fetch('https://psgc.gitlab.io/api/regions/')
        .then(res => res.json())
        .then(data => {
            data.forEach(region => {
                regionSelect.innerHTML += `<option value="${region.code}">${region.name}</option>`;
            });
        });

    regionSelect && regionSelect.addEventListener('change', function () {
        resetSelect(provinceSelect, '-- Select Province --');
        resetSelect(citySelect, '-- Select City/Municipality --');
        if (barangaySelect) resetSelect(barangaySelect, '-- Select Barangay --');
        if (!this.value) return;
        fetch(`https://psgc.gitlab.io/api/regions/${this.value}/provinces/`)
            .then(res => res.json())
            .then(data => {
                data.forEach(province => {
                    provinceSelect.innerHTML += `<option value="${province.code}">${province.name}</option>`;
                });
            });
    });

    provinceSelect && provinceSelect.addEventListener('change', function () {
        resetSelect(citySelect, '-- Select City/Municipality --');
        if (barangaySelect) resetSelect(barangaySelect, '-- Select Barangay --');
        if (!this.value) return;
        fetch(`https://psgc.gitlab.io/api/provinces/${this.value}/cities-municipalities/`)
            .then(res => res.json())
            .then(data => {
                data.forEach(city => {
                    citySelect.innerHTML += `<option value="${city.code}">${city.name}</option>`;
                });
            });
    });

    if (barangaySelect) {
        citySelect && citySelect.addEventListener('change', function () {
            resetSelect(barangaySelect, '-- Select Barangay --');
            if (!this.value) return;
            fetch(`https://psgc.gitlab.io/api/cities-municipalities/${this.value}/barangays/`)
                .then(res => res.json())
                .then(data => {
                    data.forEach(barangay => {
                        barangaySelect.innerHTML += `<option value="${barangay.name}">${barangay.name}</option>`;
                    });
                });
        });
    }
});
</script>
@endsection
