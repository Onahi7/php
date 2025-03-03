<?php
// About page for Education Summit
$pageTitle = "About Us | Education Summit";
include_once __DIR__ . '/layouts/main.php';
?>

<div class="bg-gradient-to-b from-white to-gray-100 dark:from-gray-900 dark:to-gray-800 transition-colors duration-300">
    <!-- Hero Section -->
    <div class="relative overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="relative z-10 pb-8 sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
                <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                    <div class="sm:text-center lg:text-left">
                        <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 dark:text-white sm:text-5xl md:text-6xl transition-colors duration-300">
                            <span class="block xl:inline">About the</span>
                            <span class="block text-indigo-600 dark:text-indigo-400 xl:inline transition-colors duration-300">Education Summit</span>
                        </h1>
                        <p class="mt-3 text-base text-gray-500 dark:text-gray-400 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0 transition-colors duration-300">
                            Bringing together educators, innovators, and leaders to shape the future of education through collaboration, inspiration, and actionable insights.
                        </p>
                    </div>
                </main>
            </div>
        </div>
        <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2">
            <img class="h-56 w-full object-cover sm:h-72 md:h-96 lg:w-full lg:h-full" src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1050&q=80" alt="Education conference">
        </div>
    </div>

    <!-- Our Mission Section -->
    <div class="py-16 bg-white dark:bg-gray-900 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:text-center">
                <h2 class="text-base text-indigo-600 dark:text-indigo-400 font-semibold tracking-wide uppercase transition-colors duration-300">Our Mission</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl transition-colors duration-300">
                    Transforming Education Together
                </p>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 dark:text-gray-400 lg:mx-auto transition-colors duration-300">
                    We believe in the power of collaboration to drive meaningful change in education systems worldwide.
                </p>
            </div>

            <div class="mt-10">
                <div class="space-y-10 md:space-y-0 md:grid md:grid-cols-2 md:gap-x-8 md:gap-y-10">
                    <div class="relative transition-all duration-300 transform hover:scale-105">
                        <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 dark:bg-indigo-600 text-white transition-colors duration-300">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                        </div>
                        <div class="ml-16">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white transition-colors duration-300">Global Reach</h3>
                            <p class="mt-2 text-base text-gray-500 dark:text-gray-400 transition-colors duration-300">
                                Connecting educators from around the world to share best practices and innovative approaches.
                            </p>
                        </div>
                    </div>

                    <div class="relative transition-all duration-300 transform hover:scale-105">
                        <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 dark:bg-indigo-600 text-white transition-colors duration-300">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="ml-16">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white transition-colors duration-300">Innovation Focus</h3>
                            <p class="mt-2 text-base text-gray-500 dark:text-gray-400 transition-colors duration-300">
                                Showcasing cutting-edge technologies and methodologies that are reshaping educational experiences.
                            </p>
                        </div>
                    </div>

                    <div class="relative transition-all duration-300 transform hover:scale-105">
                        <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 dark:bg-indigo-600 text-white transition-colors duration-300">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="ml-16">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white transition-colors duration-300">Inclusive Community</h3>
                            <p class="mt-2 text-base text-gray-500 dark:text-gray-400 transition-colors duration-300">
                                Creating a diverse and inclusive environment where all perspectives are valued and heard.
                            </p>
                        </div>
                    </div>

                    <div class="relative transition-all duration-300 transform hover:scale-105">
                        <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-indigo-500 dark:bg-indigo-600 text-white transition-colors duration-300">
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="ml-16">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white transition-colors duration-300">Quality Assurance</h3>
                            <p class="mt-2 text-base text-gray-500 dark:text-gray-400 transition-colors duration-300">
                                Ensuring that all content and presentations meet the highest standards of educational excellence.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Our Story Section -->
    <div class="py-16 bg-gray-50 dark:bg-gray-800 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:text-center">
                <h2 class="text-base text-indigo-600 dark:text-indigo-400 font-semibold tracking-wide uppercase transition-colors duration-300">Our Story</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl transition-colors duration-300">
                    From Vision to Reality
                </p>
            </div>
            
            <div class="mt-10">
                <div class="prose prose-indigo mx-auto text-gray-500 dark:text-gray-400 transition-colors duration-300">
                    <p class="text-lg">
                        The Education Summit began in 2010 as a small gathering of passionate educators who believed in the power of collaboration to transform education. What started as a local conference with just 50 attendees has grown into an international event attracting thousands of education professionals from over 50 countries.
                    </p>
                    
                    <p class="text-lg mt-4">
                        Our founder, Dr. Maria Rodriguez, envisioned a platform where educators could share ideas, learn from one another, and build a community dedicated to improving educational outcomes for all students. That vision continues to guide us today as we expand our reach and impact.
                    </p>
                    
                    <p class="text-lg mt-4">
                        Over the years, the Summit has been at the forefront of educational innovation, introducing participants to emerging technologies, pedagogical approaches, and research findings that have gone on to transform classrooms around the world.
                    </p>
                    
                    <p class="text-lg mt-4">
                        Today, the Education Summit stands as a testament to the power of collective wisdom and shared purpose in advancing the field of education. We remain committed to our original mission while continuously evolving to meet the changing needs of educators and learners in the 21st century.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Section -->
    <div class="py-16 bg-white dark:bg-gray-900 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:text-center">
                <h2 class="text-base text-indigo-600 dark:text-indigo-400 font-semibold tracking-wide uppercase transition-colors duration-300">Our Team</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-4xl transition-colors duration-300">
                    Meet the People Behind the Summit
                </p>
                <p class="mt-4 max-w-2xl text-xl text-gray-500 dark:text-gray-400 lg:mx-auto transition-colors duration-300">
                    Our diverse team brings together expertise from education, technology, research, and event management.
                </p>
            </div>

            <div class="mt-10">
                <div class="space-y-10 md:space-y-0 md:grid md:grid-cols-3 md:gap-x-8 md:gap-y-10">
                    <!-- Team Member 1 -->
                    <div class="group relative transition-all duration-300 transform hover:scale-105">
                        <div class="rounded-lg overflow-hidden shadow-lg">
                            <img class="w-full h-64 object-cover" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=634&q=80" alt="Dr. Maria Rodriguez">
                            <div class="px-6 py-4 bg-white dark:bg-gray-800 transition-colors duration-300">
                                <div class="font-bold text-xl mb-2 text-gray-900 dark:text-white transition-colors duration-300">Dr. Maria Rodriguez</div>
                                <p class="text-indigo-600 dark:text-indigo-400 text-base transition-colors duration-300">Founder & Executive Director</p>
                                <p class="text-gray-700 dark:text-gray-300 text-base mt-3 transition-colors duration-300">
                                    Former university dean with over 25 years of experience in educational leadership.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Team Member 2 -->
                    <div class="group relative transition-all duration-300 transform hover:scale-105">
                        <div class="rounded-lg overflow-hidden shadow-lg">
                            <img class="w-full h-64 object-cover" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=634&q=80" alt="Dr. James Chen">
                            <div class="px-6 py-4 bg-white dark:bg-gray-800 transition-colors duration-300">
                                <div class="font-bold text-xl mb-2 text-gray-900 dark:text-white transition-colors duration-300">Dr. James Chen</div>
                                <p class="text-indigo-600 dark:text-indigo-400 text-base transition-colors duration-300">Program Director</p>
                                <p class="text-gray-700 dark:text-gray-300 text-base mt-3 transition-colors duration-300">
                                    Educational technologist specializing in innovative teaching methodologies.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Team Member 3 -->
                    <div class="group relative transition-all duration-300 transform hover:scale-105">
                        <div class="rounded-lg overflow-hidden shadow-lg">
                            <img class="w-full h-64 object-cover" src="https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=634&q=80" alt="Sarah Johnson">
                            <div class="px-6 py-4 bg-white dark:bg-gray-800 transition-colors duration-300">
                                <div class="font-bold text-xl mb-2 text-gray-900 dark:text-white transition-colors duration-300">Sarah Johnson</div>
                                <p class="text-indigo-600 dark:text-indigo-400 text-base transition-colors duration-300">Operations Manager</p>
                                <p class="text-gray-700 dark:text-gray-300 text-base mt-3 transition-colors duration-300">
                                    Event management expert with a background in international education initiatives.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="bg-indigo-700 dark:bg-indigo-800 transition-colors duration-300">
        <div class="max-w-2xl mx-auto text-center py-16 px-4 sm:py-20 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                <span class="block">Ready to join us?</span>
                <span class="block">Register for the next Summit today.</span>
            </h2>
            <p class="mt-4 text-lg leading-6 text-indigo-200">
                Secure your spot at the world's premier education conference and be part of shaping the future of learning.
            </p>
            <div class="mt-8 flex justify-center">
                <div class="inline-flex rounded-md shadow">
                    <a href="<?= BASE_PATH ?>/register" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-white hover:bg-indigo-50 transition-colors duration-300">
                        Register Now
                    </a>
                </div>
                <div class="ml-3 inline-flex">
                    <a href="<?= BASE_PATH ?>/contact" class="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors duration-300">
                        Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/components/footer.php'; ?>
