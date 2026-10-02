<?php
/**
 * Default content.
 *
 * Every front-page section falls back to the values below when the site owner
 * has not yet entered their own via the Customizer or the custom post types.
 * This means the theme renders the original design on first activation.
 *
 * List fields (benefits, advantages, check lists, marquee) are stored as one
 * item per line. Icon lists use the format "icon | text".
 *
 * @package Eethal_Learning
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customizer defaults, keyed by setting id.
 *
 * @return array
 */
function eethal_defaults() {
	return array(
		// General / contact.
		'phone'                 => '+91 95859 07643',
		'whatsapp'              => '919585907643',
		'whatsapp_message'      => 'Hello eethal learings!',
		'email'                 => 'eethallearning@gmail.com',
		'enroll_url'            => '', // Empty: the built-in Enroll Now page (/enroll/).

		// Enroll Now form (/enroll/). {batch} is replaced with the batch name.
		'enroll_batch'          => 'Batch 8',
		'enroll_badge'          => '{batch} · Admissions Open',
		'enroll_title'          => 'Eethal Learning {batch} Application Form',
		'enroll_intro'          => 'Welcome to Eethal Learning (Powered by VTNF) – {batch}. Please fill out this form to register. Make sure all the details provided are accurate and complete.',
		'enroll_note'           => 'We offer quality IT skill training designed to help you adapt to current industry requirements and enhance your career opportunities.',
		'enroll_section_1'      => 'Your Details',
		'enroll_section_2'      => 'Education',
		'enroll_q_name'         => 'Name | Your full name',
		'enroll_q_email'        => 'Email | name@example.com',
		'enroll_q_mobile'       => 'Mobile Number | 10-digit number',
		'enroll_q_dob'          => 'Date of Birth',
		'enroll_q_district'     => 'District | e.g. Madurai',
		'enroll_q_referred'     => 'Referred by | Name of the person, or how you heard about us',
		'enroll_q_status'       => 'Current Status',
		'enroll_statuses'       => "I am currently studying in college.\nI am a working professional seeking new job opportunities.\nI am looking for a job.",
		'enroll_q_degree'       => 'Degree | Type your degree, e.g. Diploma, MCA',
		'enroll_degrees'        => "BE / B.Tech\nArts & Science",
		'enroll_degree_other'   => 'Other',
		'enroll_q_college'      => 'College Name | Your college name',
		'enroll_q_passed'       => 'Year of Passed Out | e.g. 2024',
		'enroll_button'         => 'Submit Application',
		'enroll_thanks_title'   => 'Thank you, {name}!',
		'enroll_thanks_text'    => 'Your application for {batch} has been received. Our team will contact you soon.',

		// Form spam protection (inc/spam-guard.php).
		'spam_turnstile_site'       => '',
		'spam_turnstile_secret'     => '',
		'spam_msg_expired'          => 'This form has expired. Please reload the page and try again.',
		'spam_msg_fast'             => 'That was quick! Please check your answers, then submit again.',
		'spam_msg_links'            => 'Please remove web links from your answers.',
		'spam_msg_captcha'          => 'Please complete the "I\'m not a robot" check.',
		'spam_msg_limit'            => 'Too many submissions from your connection. Please try again in an hour.',
		'spam_msg_duplicate_enroll' => 'You have already applied for {batch} with this email or mobile number. Our team will contact you soon.',
		'spam_msg_duplicate_entry'  => 'Your details have already been submitted and are waiting for review.',
		'particles'             => true,

		// Hero.
		'show_hero'             => true,
		'hero_eyebrow'          => '⚡ Building IT Futures from Every Corner',
		'hero_title'            => 'From Learning to Earning with Real Skills',
		'hero_desc'             => 'Start your IT career with the right guidance, real skills, and practical experience that truly makes you job-ready.',
		'hero_btn'              => 'Enroll Now',
		'hero_btn_url'          => '',

		// Benefits bar.
		'show_benefits'         => true,
		'benefits_items'        => "🎓 | 10+ Years Exp Trainers\n💻 | Real-Time Projects\n👔 | Interview Preparation\n🛠️ | 100% Practical Training",

		// Marquee.
		'show_marquee'          => true,
		'marquee_items'         => "Front-End Development\nFull Stack Development\nAdobe Experience Manager\nEdge Delivery Services\nAEM Target & Analytics\nResume Building\nMock Interviews\nCorporate Placement Support",

		// About.
		'show_about'            => true,
		'about_label'           => 'Our Mission',
		'about_title'           => "Transforming Lives Through\nPractical IT Training",
		'about_text_1'          => 'Started during the 2020 pandemic, this initiative has grown over 6 years and 7 successful batches — turning uncertain times into powerful career transformations for our students.',
		'about_text_2'          => 'We started with a mission to support village and underprivileged students — training them without fees in the beginning. Today, we continue the same mission with affordable, high-quality training that helps students build real skills and achieve successful IT careers.',
		'about_checklist'       => "Industry-Level Mentorship\nReal-Time Project Experience\nPlacement-Focused Training",

		// Shared stats.
		'stat_students'         => 106,
		'stat_students_suffix'  => '+',
		'stat_students_label'   => 'Students Trained',
		'stat_placement'        => 97,
		'stat_placement_suffix' => '%',
		'stat_placement_label'  => 'Placement Rate',

		// Courses.
		'show_courses'          => true,
		'courses_label'         => 'Our Courses',
		'courses_title'         => 'Training Specifically For Your Success',
		'course_btn_text'       => 'Learn More',

		// Why choose us.
		'show_why_choose_us'    => true,
		'why_label'             => 'Our Advantages',
		'why_title'             => 'Why Choose Us',
		'why_items'             => "💻 | Real-Time Projects\n👨‍🏫 | Experienced Trainers\n🛠️ | Practical Learning\n📝 | Resume & Interview Support\n🤝 | Continuous Mentorship",

		// Bonding.
		'bonding_enable'        => true,
		'bonding_label'         => 'Bonding',
		'bonding_title'         => 'Stay Connect With Us',

		// Outcomes.
		'show_outcomes'         => true,
		'outcomes_label'        => 'Student Outcomes',
		'outcomes_title'        => 'Turning Challenges into Career Breakthroughs',
		'outcomes_text'         => 'While others paused during COVID, Eethal students took action. They upskilled, stayed focused, and transformed their future. Today, they are not just working — they are leading successful careers in the IT industry. At Eethal, challenges become opportunities.',
		'outcomes_checklist'    => "Build Real-World Projects\nGain Interview Confidence\nIndustry Exposure",
		'outcomes_stat_1_label' => 'Students Trained',
		'outcomes_stat_2_label' => 'Successful Placements',

		// Mentors.
		'show_mentors'          => true,
		'mentors_label'         => 'Your Mentors',
		'mentors_title'         => 'Where Students Become Mentors',
		'mentors_sub'           => 'Our pride — students turned mentors. Now thriving in top MNCs, they lead by example and uplift others to achieve greater success.',

		// Testimonials.
		'show_testimonials'     => true,
		'test_label'            => 'Alumni Stories',
		'test_title'            => 'Real Outcomes. Real People.',

		// FAQ.
		'show_faq'              => true,
		'faq_label'             => 'FAQ',
		'faq_title'             => 'Got Questions?',

		// CTA.
		'show_cta'              => true,
		'cta_title'             => 'Ready to Start Your IT Career?',
		'cta_text'              => 'Join our next batch and transform your future with industry-led training.',
		'cta_btn'               => 'Enroll Now',
		'cta_btn_url'           => '',
		'cta_btn_2'             => 'Contact Us',
		'cta_btn_2_url'         => '',
		'cta_brand'             => 'Eethal learning',
		'cta_phone_label'       => 'Call:',
		'cta_email_label'       => 'Email:',

		// Footer.
		'footer_text'           => 'Eethal learning powered by VTNF.',
	);
}

/**
 * Default courses, imported as Course posts on first activation.
 *
 * @return array
 */
function eethal_default_courses() {
	return array(
		array(
			'title' => 'Front-End Development',
			'desc'  => 'Learn HTML, CSS & Javascript',
			'image' => 'assets/images/course-frontend.png',
		),
		array(
			'title' => 'AEM Front-End',
			'desc'  => 'AEM Templates & Components',
			'image' => 'assets/images/course-aem-frontend.png',
		),
		array(
			'title' => 'AEM EDS',
			'desc'  => 'Edge Delivery & Optimization',
			'image' => 'assets/images/course-aem-eds.png',
		),
		array(
			'title' => 'AEM Target & Analytics',
			'desc'  => 'Personalization & Tracking',
			'image' => 'assets/images/course-aem-analytics.png',
		),
	);
}

/**
 * Default mentors, imported as Mentor posts on first activation.
 *
 * @return array
 */
function eethal_default_mentors() {
	return array(
		array(
			'name'     => 'Jeganath',
			'initials' => 'JN',
			'colour'   => 'ia1',
			'role'     => 'LEAD SOFTWARE ENGINEER',
			'bio'      => '15+ years of experience in front-end development, specializing in building scalable and high-performance UI solutions.',
			'tags'     => array( 'HTML5', 'CSS3', 'JavaScript' ),
		),
		array(
			'name'     => 'Mohammed Nagoor',
			'initials' => 'MN',
			'colour'   => 'ia2',
			'role'     => 'SENIOR AEM Developer',
			'bio'      => '10+ years in Adobe Experience Manager and Edge Delivery Services, specializing in enterprise Adobe solutions.',
			'tags'     => array( 'AEM', 'EDS', 'JavaScript' ),
		),
		array(
			'name'     => 'Soundhara Rajan',
			'initials' => 'SR',
			'colour'   => 'ia2',
			'role'     => 'SENIOR ASSOCIATE',
			'bio'      => '7+ years of experience in front-end technologies and Edge Delivery Services (EDS), delivering scalable, and high-performance solutions.',
			'tags'     => array( 'AEM', 'EDS', 'JavaScript' ),
		),
		array(
			'name'     => 'Raja Guru',
			'initials' => 'RG',
			'colour'   => 'ia3',
			'role'     => 'Senior Software Engineer',
			'bio'      => '6+ years in AEM, Adobe Target & Analytics, and front-end development, focused on personalization.',
			'tags'     => array( 'Adobe Target', 'Analytics' ),
		),
		array(
			'name'     => 'Guru Moorthi',
			'initials' => 'GM',
			'colour'   => 'ia1',
			'role'     => 'SOFTWARE ENGINEER',
			'bio'      => '6+ years of experience in front-end technologies, focused on building responsive and dynamic web applications.',
			'tags'     => array( 'HTML5', 'CSS3', 'JavaScript' ),
		),
		array(
			'name'     => 'Deva',
			'initials' => 'DV',
			'colour'   => 'ia3',
			'role'     => 'SENIOR ARCHITECT',
			'bio'      => 'Senior Architect with 15+ years delivering scalable enterprise platforms, specializing in governance and program management.',
			'tags'     => array( 'Governance', 'Management' ),
		),
	);
}

/**
 * Default testimonials, imported as Testimonial posts on first activation.
 *
 * @return array
 */
function eethal_default_testimonials() {
	return array(
		array(
			'name'     => 'Priya',
			'initials' => 'PR',
			'colour'   => 'av-r',
			'role'     => 'Software Engineer',
			'quote'    => 'As a proud Eethal learning Batch 6 student, I’m happy to share my placement with a great package. Thanks to Eethal learning. Truly grateful to Eethal learning for their incredible support.',
		),
		array(
			'name'     => 'Prasath',
			'initials' => 'PR',
			'colour'   => 'av-i',
			'role'     => 'Associate',
			'quote'    => 'Eethal learning offers professional front-end training with clear understanding, practical exposure, and continuous support. This experience not only strengthens skills but also gives the confidence and direction needed to achieve a successful career.',
		),
		array(
			'name'     => 'Rajkumar',
			'initials' => 'RA',
			'colour'   => 'av-g',
			'role'     => 'Senior Web Engineer',
			'quote'    => 'I started with zero knowledge, but Eethal completely transformed my confidence from day one. With exceptional guidance from Trainers, learning became clear, practical, and impactful. Unlike my past experience, here I received true support, personal attention, and real growth. Eethal didn’t just change my career—it changed my life.',
		),
		array(
			'name'     => 'Krishna',
			'initials' => 'KR',
			'colour'   => 'av-r',
			'role'     => 'Software developer',
			'quote'    => 'Eethal learning offers quality front-end training with clear concepts, practical learning, and supportive trainers. Flexible timings and placement support help learners build strong fundamentals in HTML, CSS, and JavaScript.',
		),
		array(
			'name'     => 'Priyanka',
			'initials' => 'PR',
			'colour'   => 'av-i',
			'role'     => 'AEM Developer',
			'quote'    => 'Though I come from an Arts and Science background, my transformation began at Eethal. With the right guidance and practical training, I built strong front-end skills. Today, I’m in the IT field, earning well and growing confidently. Eethal truly changed my life, and I’m deeply grateful for the opportunity.',
		),
		array(
			'name'     => 'Amudha',
			'initials' => 'AM',
			'colour'   => 'av-g',
			'role'     => 'Senior Web Engineer',
			'quote'    => 'The Eethal program really helped me with career development and upgrading my website development skills. The training was practical and matched what companies need today.',
		),
		array(
			'name'     => 'Nathiya',
			'initials' => 'NA',
			'colour'   => 'av-r',
			'role'     => 'Senior Associate',
			'quote'    => 'A life-changing mentorship experience. From a non-IT background to a Front-End Developer in 3 months, the journey was intense yet rewarding, with every session adding real value and confidence.',
		),
		array(
			'name'     => 'Rakesh',
			'initials' => 'RA',
			'colour'   => 'av-i',
			'role'     => 'AEM-EDS Developer',
			'quote'    => 'Eethal classes transformed my life. You don’t need any prior experience—even knowing how to turn on a laptop is enough. With the guidance and training here, you can grow into a top developer.',
		),
		array(
			'name'     => 'Kumar',
			'initials' => 'KU',
			'colour'   => 'av-g',
			'role'     => 'Senior Software Engineer',
			'quote'    => 'I sincerely appreciate the Eethal sessions for strengthening my front-end skills. The clear, structured teaching of HTML, CSS, and JavaScript made even complex concepts easy to understand. Real-time examples and hands-on practice improved my problem-solving and confidence. The supportive trainers and interactive approach made the learning experience truly valuable.',
		),
	);
}

/**
 * Default FAQs, imported as FAQ posts on first activation.
 *
 * @return array
 */
function eethal_default_faqs() {
	return array(
		array(
			'q' => 'Who is this training designed for?',
			'a' => 'Our training is designed for three types of learners — students who are currently studying, working professionals who are employed, and job seekers who are on a career break or actively searching for opportunities. Each group receives a customized learning experience tailored to their schedule, needs, and career goals.',
		),
		array(
			'q' => 'Are sessions recorded for later review?',
			'a' => 'Yes, every live session is recorded in HD and shared with students for lifetime access. You can revisit any concept at any time, even after course completion.',
		),
		array(
			'q' => 'What does job assistance include?',
			'a' => 'Full placement support: professionally reviewed resume, 3+ mock interviews with detailed feedback, LinkedIn profile optimisation, and direct referrals via our 50+ corporate partner network.',
		),
		array(
			'q' => 'Do I receive a certificate on completion?',
			'a' => 'Yes. Upon successfully completing the course and your capstone live project, you receive a Eethal Training Certificate — recognised and valued by our corporate partners.',
		),
		array(
			'q' => 'What are the batch timings?',
			'a' => 'We offer morning (6–8 AM), morning (7–9 AM), evening (7–9 PM), and weekend batches to suit working professionals and students. A review call will be conducted once a month with our senior mentor.',
		),
		array(
			'q' => 'Is there an EMI or payment plan available?',
			'a' => 'Yes, we offer flexible 2–3 month EMI options at zero extra cost. Contact our counsellor to set up a payment plan that works for your situation.',
		),
	);
}
