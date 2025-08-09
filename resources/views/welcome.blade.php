@extends('layouts.publicApp')

@section('content')
    <div class="container py-5 mt-5">
        <div class="row align-items-center mt-5">
            <div class="col-md-6 text-center text-md-start">
                <img src="{{ asset('images/GabayHealthLight.png') }}" alt="Gabay Health logo" style="width: 120px;"
                    class="mb-3">
                <h1 class="display-4 fw-bold mb-3" style="color: #1657c1;">Welcome to Gabay Health</h1>
                <p class="lead mb-4" style="font-size: 1.25rem;">
                    Bringing Public Health Closer to Home.
                </p>
                <div class="d-flex gap-3 justify-content-center justify-content-md-start">
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-4"
                        aria-label="Get started with Gabay Health">Get started</a>
                    <a href="#about" class="btn btn-outline-primary btn-lg px-4"
                        aria-label="Learn more about Gabay Health">Learn more</a>
                </div>
            </div>
            <div class="col-md-6 text-center mt-5 mt-md-0">
                <img src="{{ asset('images/GabayHealthMockup.png') }}" alt="Gabay Health app preview" class="img-fluid"
                    style="max-width: 420px;">
            </div>
        </div>

        <hr class="my-5">

        <div id="about" class="row g-4 align-items-center">
            <div class="col-lg-7">
                <h2 class="fw-semibold mb-3">What is Gabay Health?</h2>
                <p class="mb-3 text-muted">
                    Gabay Health is a community-centered platform that helps families access trusted public health services
                    faster. Use one simple app to find clinics, book appointments, join health programs, and receive timely
                    reminders—wherever you are.
                </p>
                <ul class="list-unstyled">
                    <li class="d-flex mb-2">
                        <span class="badge rounded-pill bg-primary me-3">1</span>
                        <div>
                            <strong>Find nearby care</strong>
                            <div class="text-muted small">See open clinics, vaccination sites, and outreach schedules.</div>
                        </div>
                    </li>
                    <li class="d-flex mb-2">
                        <span class="badge rounded-pill bg-primary me-3">2</span>
                        <div>
                            <strong>Book and manage visits</strong>
                            <div class="text-muted small">Reserve slots, check requirements, and reschedule easily.</div>
                        </div>
                    </li>
                    <li class="d-flex">
                        <span class="badge rounded-pill bg-primary me-3">3</span>
                        <div>
                            <strong>Stay on track</strong>
                            <div class="text-muted small">Get reminders for immunizations, follow-ups, and screenings.</div>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="col-lg-5">
                <div class="p-4 border rounded-3 h-100 bg-light">
                    <h3 class="h5 mb-3">Why choose Gabay Health</h3>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="d-flex">
                                <div class="me-3 text-primary">✓</div>
                                <div>
                                    <strong>Community-first</strong>
                                    <div class="text-muted small">Built with local health partners and programs.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex">
                                <div class="me-3 text-primary">✓</div>
                                <div>
                                    <strong>Secure by design</strong>
                                    <div class="text-muted small">Your data is encrypted and handled responsibly.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex">
                                <div class="me-3 text-primary">✓</div>
                                <div>
                                    <strong>Multi-language</strong>
                                    <div class="text-muted small">Accessible to diverse communities and households.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <a href="{{ route('login') }}" class="btn btn-primary w-100">Create an account</a>
                        {{-- <a href="#faq" class="btn btn-outline-secondary w-100">How it works</a> --}}
                    </div>
                </div>
            </div>
        </div>

        <div id="highlights" class="row row-cols-1 row-cols-md-3 g-4 mt-4">
            <div class="col">
                <div class="h-100 p-4 border rounded-3">
                    <h4 class="h6 mb-2 text-primary text-uppercase">Programs</h4>
                    <p class="mb-0 text-muted">Immunization drives, maternal care, nutrition, and community screenings.</p>
                </div>
            </div>
            <div class="col">
                <div class="h-100 p-4 border rounded-3">
                    <h4 class="h6 mb-2 text-primary text-uppercase">Reminders</h4>
                    <p class="mb-0 text-muted">Timely alerts so families never miss important health dates.</p>
                </div>
            </div>
            <div class="col">
                <div class="h-100 p-4 border rounded-3">
                    <h4 class="h6 mb-2 text-primary text-uppercase">Support</h4>
                    <p class="mb-0 text-muted">Reach local health workers and get help with requirements and forms.</p>
                </div>
            </div>
        </div>

        {{-- <div id="faq" class="mt-5">
            <h2 class="fw-semibold mb-3">Frequently asked questions</h2>
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="q1">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#a1"
                            aria-expanded="true" aria-controls="a1">
                            Is Gabay Health free to use?
                        </button>
                    </h2>
                    <div id="a1" class="accordion-collapse collapse show" aria-labelledby="q1"
                        data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes. Access to public health program information and scheduling is free for individuals and
                            families.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="q2">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#a2" aria-expanded="false" aria-controls="a2">
                            What data do you collect?
                        </button>
                    </h2>
                    <div id="a2" class="accordion-collapse collapse" aria-labelledby="q2"
                        data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Only the information needed to book services and send reminders. We protect your data in line
                            with applicable regulations.
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
@endsection
