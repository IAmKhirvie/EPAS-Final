<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Contact - EPAS-E LMS</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ dynamic_asset('favicon.ico') }}">

    <!-- Google Fonts - Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS (local) -->
    <link href="{{ dynamic_asset('vendor/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome (local) -->
    <link rel="stylesheet" href="{{ dynamic_asset('vendor/css/fontawesome.min.css') }}">

    <!-- App CSS -->
    <link rel="stylesheet" href="{{ dynamic_asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/base/reset.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/base/typography.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/layout/header.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/layout/public-header.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/pages/info.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/components/buttons.css') }}">
    <link rel="stylesheet" href="{{ dynamic_asset('css/components/utilities.css') }}">

    @vite(['resources/css/app.css'])
</head>
<body class="auth-page-body">
    @include('partials.header')

    <!-- Hero Banner -->
    <section class="page-hero">
        <div class="page-hero-badge"><i class="fas fa-paper-plane me-1"></i> Get In Touch</div>
        <h1>Contact Us</h1>
        <p>Have a question about EPAS NC II or enrollment? We'd love to hear from you</p>
    </section>

    <!-- Main Content -->
    <main class="info-page">
        <div class="info-container">

            <!-- Map Section -->
            <section class="info-section">
                <div class="info-section-header">
                    <div class="section-icon"><i class="fas fa-map-marked-alt"></i></div>
                    <div>
                        <h2>Campus Location</h2>
                        <p>34 Lark Street, Sta. Elena, Marikina City</p>
                    </div>
                </div>
                <div class="contact-map-container">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d925.7822268843197!2d121.09956074646428!3d14.634067983489432!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b835490659d5%3A0x6574ba3f78ebdd32!2sIETI%20College%20of%20Science%20and%20Technology%2C%20Marikina%2C%20Inc.!5e0!3m2!1sen!2sph!4v1769575366881!5m2!1sen!2sph" width="100%" height="400" style="border:0; border-radius: 12px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </section>

            <!-- Contact Info & Inquiry Form -->
            <section class="info-section">
                <div class="row g-4">
                    <!-- Contact Information -->
                    <div class="col-lg-5">
                        <div class="contact-card h-100">
                            <div class="contact-icon">
                                <i class="fas fa-school"></i>
                            </div>
                            <h4 class="mb-4 text-center">IETI College Marikina</h4>

                            <div class="text-start">
                                <div class="contact-detail-item">
                                    <h6><i class="fas fa-map-marker-alt text-primary me-2"></i>Address</h6>
                                    <p class="mb-0">34 Lark Street, Sta. Elena, Marikina City, Philippines</p>
                                </div>

                                <div class="contact-detail-item">
                                    <h6><i class="fas fa-phone text-primary me-2"></i>Phone Numbers</h6>
                                    <p class="mb-1">Mobile: 0917-120-7428</p>
                                    <p class="mb-0">Landline: 868-16-431</p>
                                </div>

                                <div class="contact-detail-item">
                                    <h6><i class="fas fa-envelope text-primary me-2"></i>Email</h6>
                                    <p class="mb-0"><a href="mailto:ietimarikina8@yahoo.com">ietimarikina8@yahoo.com</a></p>
                                </div>

                                <div class="contact-detail-item">
                                    <h6><i class="fab fa-facebook text-primary me-2"></i>Facebook</h6>
                                    <p class="mb-0"><a href="https://www.facebook.com/ieti.marikina" target="_blank">IETI Marikina</a></p>
                                </div>

                                <div class="contact-detail-item">
                                    <h6><i class="fas fa-clock text-primary me-2"></i>Office Hours</h6>
                                    <p class="mb-1">Monday - Friday: 8:00 AM - 5:00 PM</p>
                                    <p class="mb-0">Saturday: 8:00 AM - 12:00 PM</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inquiry Form -->
                    <div class="col-lg-7">
                        <div class="contact-card h-100">
                            <div class="contact-icon">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <h4 class="mb-4 text-center">Send Us an Inquiry</h4>

                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('contact.submit') }}" class="text-start">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                    <select class="form-select @error('subject') is-invalid @enderror" id="subject" name="subject" required>
                                        <option value="">Select a subject...</option>
                                        <option value="Enrollment Inquiry" {{ old('subject') == 'Enrollment Inquiry' ? 'selected' : '' }}>Enrollment Inquiry</option>
                                        <option value="EPAS NC II Program" {{ old('subject') == 'EPAS NC II Program' ? 'selected' : '' }}>EPAS NC II Program Information</option>
                                        <option value="TESDA Assessment" {{ old('subject') == 'TESDA Assessment' ? 'selected' : '' }}>TESDA Assessment Schedule</option>
                                        <option value="Tuition & Fees" {{ old('subject') == 'Tuition & Fees' ? 'selected' : '' }}>Tuition & Fees</option>
                                        <option value="LMS Technical Support" {{ old('subject') == 'LMS Technical Support' ? 'selected' : '' }}>LMS Technical Support</option>
                                        <option value="Other" {{ old('subject') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="5" placeholder="Please describe your inquiry in detail..." required>{{ old('message') }}</textarea>
                                    @error('message')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Send Inquiry
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQ Section -->
            <section class="info-section">
                <div class="info-section-header">
                    <div class="section-icon"><i class="fas fa-question-circle"></i></div>
                    <h2>Frequently Asked Questions</h2>
                </div>

                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                What is EPAS NC II?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                EPAS NC II (Electronic Products Assembly and Servicing National Certificate Level II) is a TESDA-certified program that trains students in assembling, testing, and servicing electronic products. It covers consumer electronics, industrial electronics modules, and computer systems.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                What are the requirements for enrollment?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <ul class="mb-0">
                                    <li>Must be at least 18 years old</li>
                                    <li>High school graduate or equivalent</li>
                                    <li>Able to communicate in English and Filipino</li>
                                    <li>Physically and mentally fit</li>
                                    <li>Birth certificate (PSA)</li>
                                    <li>High school diploma or Form 137</li>
                                    <li>2x2 ID photos (4 pieces)</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                How long is the EPAS NC II program?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                The EPAS NC II program typically takes 268 hours of training, which can be completed in approximately 2-3 months depending on the schedule. This includes both classroom instruction and hands-on laboratory practice.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Is the program TESDA accredited?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, IETI College Marikina is a TESDA-accredited institution. Upon successful completion of the program and passing the assessment, you will receive a National Certificate (NC II) from TESDA, which is recognized nationwide and can help you secure employment in the electronics industry.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                How do I access the online learning platform (LMS)?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                After enrollment, you will receive login credentials from your instructor. Visit the login page and enter your email and password. If you're having trouble logging in, contact your instructor or send us an inquiry through this page.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                What are the job opportunities after completing EPAS NC II?
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Graduates can work as:
                                <ul class="mt-2 mb-0">
                                    <li>Electronics Assembler</li>
                                    <li>Electronics Technician</li>
                                    <li>Service Technician</li>
                                    <li>Quality Control Inspector</li>
                                    <li>Computer Technician</li>
                                    <li>Manufacturing Operator</li>
                                </ul>
                                Many graduates find employment in electronics manufacturing companies, service centers, and IT companies.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                Are there scholarships available?
                            </button>
                        </h2>
                        <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, TESDA offers the Training for Work Scholarship Program (TWSP) and the Special Training for Employment Program (STEP) which may cover tuition for qualified students. Contact our admissions office for more details on available scholarships and how to apply.
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Other Campus -->
            <section class="info-section">
                <div class="info-section-header">
                    <div class="section-icon"><i class="fas fa-building"></i></div>
                    <h2>Other IETI Campus</h2>
                </div>
                <div class="contact-info">
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h6><i class="fas fa-map-marker-alt me-2"></i>IETI Alabang Campus</h6>
                            <p class="mb-1">No. 5 Molina St., Alabang, Muntinlupa City</p>
                            <p class="mb-1">Phone: (02) 850 0937</p>
                            <a href="https://www.facebook.com/ietialabang74" target="_blank" class="btn btn-outline-primary btn-sm mt-2">
                                <i class="fab fa-facebook me-1"></i>Visit Facebook Page
                            </a>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-info-circle me-2"></i>Program Offered</h6>
                            <p class="mb-1">EPAS NC II - Electronics Products Assembly and Servicing</p>
                            <p class="mb-0 text-muted">TESDA Accredited Program</p>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </main>

    @include('partials.footer')

    @auth
    @include('components.bottom-nav')
    @endauth

    <!-- Bootstrap JS (local) -->
    <script src="{{ dynamic_asset('vendor/js/bootstrap.bundle.min.js') }}"></script>

    <script src="{{ dynamic_asset('js/utils/dark-mode.js')}}"></script>
    <script src="{{ dynamic_asset('js/components/public-darkmode.js')}}"></script>

    @guest
    <script src="{{ dynamic_asset('js/public-header.js')}}"></script>
    @endguest

    @auth
    <script src="{{ dynamic_asset('js/public-header-auth.js')}}"></script>
    @endauth
</body>
</html>
