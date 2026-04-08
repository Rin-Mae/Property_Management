<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/NC Logo.png') }}">
    <title>Mint Crest Hotel - Property Management</title>
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="Mint Crest Hotel Logo" style="height: 3rem;">

            </div>
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#rooms">Rooms</a>
                <a href="#amenities">Amenities</a>
                <a href="#about">About</a>
                <a href="#contact">Contact</a>
                <button class="btn-login" onclick="openLoginModal()">Login</button>
            </div>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to Mint Crest Hotel</h1>
            <p>Elegant Hotel Management System for Modern Hospitality</p>
            <div class="hero-buttons">
                <button onclick="openRegisterModal()" class="btn btn-primary">Get Started</button>
                <a href="#features" class="btn btn-secondary">Learn More</a>
            </div>
        </div>
        <div class="hero-image">
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <h2>Why Choose LuxeStay?</h2>
            <p class="section-subtitle">Comprehensive hotel management solutions tailored for your property</p>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <h3>Room Reservations</h3>
                    <p>Effortless booking and management system with real-time availability updates and instant
                        confirmations.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <h3>Room Management</h3>
                    <p>Complete control over room inventory, types, rates, and status tracking with intuitive
                        interfaces.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Amenities Tracking</h3>
                    <p>Organize and manage all hotel amenities, from WiFi to spa services, with comprehensive mapping.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Guest Management</h3>
                    <p>Maintain detailed guest profiles, preferences, and history for personalized service delivery.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h3>Maintenance Tracking</h3>
                    <p>Schedule and monitor maintenance activities ensuring your property remains in prime condition.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h3>Secure Access</h3>
                    <p>Token-based authentication and role-based permissions ensure data security and privacy.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Rooms Section -->
    <section id="rooms" class="rooms">
        <div class="container">
            <h2>Our Room Types</h2>
            <p class="section-subtitle">Choose from our diverse selection of comfortable accommodations</p>

            <div class="rooms-grid">
                <div class="room-card">
                    <div class="room-image single-room">
                        <img src="{{ asset('images/standard/1000481934.jpg') }}" alt="Standard Room"
                            class="room-image-img">
                    </div>
                    <div class="room-info">
                        <h3>Standard Room</h3>
                        <p>Perfect for single travelers and short stays with essential amenities.</p>
                        <ul class="room-features">
                            <li><i class="fas fa-wifi"></i> Free WiFi</li>
                            <li><i class="fas fa-tv"></i> Smart TV</li>
                            <li><i class="fas fa-air-freshener"></i> Air Conditioning</li>
                        </ul>
                    </div>
                </div>

                <div class="room-card">
                    <div class="room-image deluxe-room">
                        <img src="{{ asset('images/deluxe/1000481935.jpg') }}" alt="Deluxe Room" class="room-image-img">
                    </div>
                    <div class="room-info">
                        <h3>Deluxe Room</h3>
                        <p>Spacious rooms with premium furnishings and modern conveniences.</p>
                        <ul class="room-features">
                            <li><i class="fas fa-wifi"></i> Free WiFi</li>
                            <li><i class="fas fa-coffee"></i> Mini Bar</li>
                            <li><i class="fas fa-bathtub"></i> Bath Tub</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Amenities Section -->
    <section id="amenities" class="amenities">
        <div class="container">
            <h2>Hotel Amenities</h2>
            <p class="section-subtitle">Enjoy world-class facilities and services</p>

            <div class="amenities-grid">
                <div class="amenity-item">
                    <i class="fas fa-swimming-pool"></i>
                    <h4>Swimming Pool</h4>
                </div>
                <div class="amenity-item">
                    <i class="fas fa-dumbbell"></i>
                    <h4>Fitness Center</h4>
                </div>
                <div class="amenity-item">
                    <i class="fas fa-spa"></i>
                    <h4>Spa & Wellness</h4>
                </div>
                <div class="amenity-item">
                    <i class="fas fa-utensils"></i>
                    <h4>Fine Dining</h4>
                </div>
                <div class="amenity-item">
                    <i class="fas fa-wifi"></i>
                    <h4>High-Speed WiFi</h4>
                </div>
                <div class="amenity-item">
                    <i class="fas fa-car"></i>
                    <h4>Parking</h4>
                </div>
                <div class="amenity-item">
                    <i class="fas fa-calendar-check"></i>
                    <h4>Event Space</h4>
                </div>
                <div class="amenity-item">
                    <i class="fas fa-concierge-bell"></i>
                    <h4>Concierge Service</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stat">
                <h3>500+</h3>
                <p>Guest Rooms</p>
            </div>
            <div class="stat">
                <h3>50+</h3>
                <p>Premium Amenities</p>
            </div>
            <div class="stat">
                <h3>98%</h3>
                <p>Guest Satisfaction</p>
            </div>
            <div class="stat">
                <h3>24/7</h3>
                <p>Customer Support</p>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <h2>About Us</h2>
            <p class="section-subtitle">Meet the visionary leadership and talented team behind this innovative system
            </p>

            <!-- Dean Section -->
            <div class="about-subsection dean-section">
                <h3>Dean of Bachelor of Information Technology</h3>
                <div class="dean-content">
                    <div class="dean-image">
                        <img src="{{ asset('images/dean1.jpg') }}" alt="Dean" class="about-image">
                    </div>
                    <div class="dean-intro">
                        <p>Ma’am Loida Hermosura a dedicated and passionate educator known for her commitment to
                            academic excellence and student development. With years of experience in teaching and
                            guiding learners, she has consistently inspired students to reach their full potential
                            through her supportive and engaging approach.</p>
                        <p>Her expertise, combined with her genuine care for her students’ growth, makes her a respected
                            figure in the academic community. Ma’am Hermosura continues to play a vital role in shaping
                            future professionals by fostering a learning environment built on discipline, knowledge, and
                            integrity.</p>
                    </div>
                </div>
            </div>

            <!-- Developers Team Section -->
            <div class="about-subsection team-section">
                <h3>Our Development Team</h3>
                <div class="team-image-container">
                    <img src="{{ asset('images/image1.jpg') }}" alt="Development Team Member 1"
                        class="about-image-full">
                    <img src="{{ asset('images/image2.jpg') }}" alt="Development Team Member 2"
                        class="about-image-full">
                    <img src="{{ asset('images/HmDean.jpg') }}" alt="HM Dean" class="about-image-full">
                </div>
            </div>

            <!-- About Area Section -->
            <div class="about-subsection about-area-section">
                <h3>Why We Built This System</h3>
                <div class="about-area-content">
                    <div class="about-area-text">
                        <h4>Our Mission</h4>
                        <p>We recognized the need for a comprehensive, user-friendly hotel property management system
                            that could streamline operations for modern hospitality businesses. Our goal was to create a
                            solution that simplifies complex booking processes, enhances guest experiences, and provides
                            hotel managers with powerful tools for efficient property management.</p>
                    </div>
                    <div class="about-area-text">
                        <h4>Who It Serves</h4>
                        <p>This system is designed for:</p>
                        <ul class="about-list">
                            <li><strong>Hotel Managers:</strong> To efficiently manage room inventory, reservations, and
                                guest relations</li>
                            <li><strong>Admin Staff:</strong> To oversee operations, handle bookings, and manage
                                maintenance schedules</li>
                            <li><strong>Guests:</strong> To enjoy seamless booking experiences and personalized service
                            </li>
                            <li><strong>Educational Institutions:</strong> As a learning platform for hospitality and
                                information technology students</li>
                        </ul>
                    </div>
                    <div class="about-area-text">
                        <h4>Our Commitment</h4>
                        <p>We are committed to providing a reliable, scalable, and innovative platform that sets the
                            standard for hotel property management systems. Through continuous improvement and user
                            feedback, we strive to deliver excellence in every aspect of our service.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Ready to Experience Luxury?</h2>
            <p>Join thousands of properties using LuxeStay for seamless management</p>
            <div class="cta-buttons">
                <button onclick="openRegisterModal()" class="btn btn-primary btn-lg">Start Your Journey</button>
                <a href="#contact" class="btn btn-secondary btn-lg">Get More Information</a>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <h2>Get In Touch</h2>
            <p class="section-subtitle">Have questions? We're here to help</p>

            <div class="contact-content">
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h4>Location</h4>
                            <p>123 Hospitality Street, City Center</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h4>Phone</h4>
                            <p>+63 912-345-6789</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>Email</h4>
                            <p>info@luxestay.com</p>
                        </div>
                    </div>
                </div>

                <form class="contact-form" id="contactForm">
                    @csrf
                    <div class="form-group">
                        <input type="text" placeholder="Your Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" placeholder="Your Email" required>
                    </div>
                    <div class="form-group">
                        <textarea placeholder="Your Message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h4>Mint Crest Hotel</h4>
                    <p>Premium hotel property management solutions</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#rooms">Rooms</a></li>
                        <li><a href="#amenities">Amenities</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Cookie Policy</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Follow Us</h4>
                    <div class="social-links">
                        <a href="#" class="social-icon"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-linkedin"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 Mint Crest Hotel. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Login to Mint Crest Hotel</h2>
                <button class="close-btn" onclick="closeLoginModal()">&times;</button>
            </div>
            <form id="loginForm" onsubmit="handleLoginSubmit(event)">
                @csrf
                <div class="form-group">
                    <label for="loginEmail">Email Address</label>
                    <input type="email" id="loginEmail" name="email" required>
                    <div class="error-message" id="emailError"></div>
                </div>

                <div class="form-group">
                    <label for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" name="password" required>
                    <div class="error-message" id="passwordError"></div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>

            <div style="text-align: center; margin-top: 1rem;">
                <p>Don't have an account? <a href="#" onclick="switchToRegister()"
                        style="color: #00a516; text-decoration: none; font-weight: bold;">Register here</a></p>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Register with Mint Crest Hotel</h2>
                <button class="close-btn" onclick="closeRegisterModal()">&times;</button>
            </div>
            <form id="registerForm" onsubmit="handleRegisterSubmit(event)">
                @csrf
                <div class="form-row">
                    <div class="form-group form-col-2-1">
                        <label for="registerFirstName">First Name *</label>
                        <input type="text" id="registerFirstName" name="first_name" required>
                        <div class="error-message" id="firstNameError"></div>
                    </div>

                    <div class="form-group form-col-2-2">
                        <label for="registerMiddleName">Middle Name</label>
                        <input type="text" id="registerMiddleName" name="middle_name">
                        <div class="error-message" id="middleNameError"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group form-col-2-1">
                        <label for="registerLastName">Last Name *</label>
                        <input type="text" id="registerLastName" name="last_name" required>
                        <div class="error-message" id="lastNameError"></div>
                    </div>

                    <div class="form-group form-col-2-2">
                        <label for="registerSuffix">Suffix</label>
                        <input type="text" id="registerSuffix" name="suffix" placeholder="e.g. Jr., Sr., III">
                        <div class="error-message" id="suffixError"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group form-col-2-1">
                        <label for="registerEmail">Email Address *</label>
                        <input type="email" id="registerEmail" name="email" required>
                        <div class="error-message" id="registerEmailError"></div>
                    </div>

                    <div class="form-group form-col-2-2">
                        <label for="registerContactNumber">Contact Number</label>
                        <input type="tel" id="registerContactNumber" name="contact_number" placeholder="Optional">
                        <div class="error-message" id="contactNumberError"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group form-col-2-1">
                        <label for="registerPassword">Password *</label>
                        <input type="password" id="registerPassword" name="password" required>
                        <div class="error-message" id="registerPasswordError"></div>
                    </div>

                    <div class="form-group form-col-2-2">
                        <label for="registerPasswordConfirm">Confirm Password *</label>
                        <input type="password" id="registerPasswordConfirm" name="password_confirmation" required>
                        <div class="error-message" id="confirmPasswordError"></div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
            </form>

            <div style="text-align: center; margin-top: 1rem;">
                <p>Already have an account? <a href="#" onclick="switchToLogin()"
                        style="color: #00a516; text-decoration: none; font-weight: bold;">Login here</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('js/landing.js') }}"></script>
</body>

</html>