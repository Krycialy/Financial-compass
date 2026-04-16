<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Compass: Navigate Your Budgeting Journey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="index.css?v=9">
    <link rel="icon" href="SAD.jpg" type="image/x-icon"/>
</head>
<body>
    <header>
    
    <nav class="navbar">
        <div class="logo">
            <i class="fa fa-compass"></i>
            <span>FINANCIAL COMPASS</span>
        </div>
        <div class="hamburger">
        ☰ <!-- Hamburger icon -->
    </div>

    <div class="sidebar">
        <a href="#hero" class="active">HOME</a>
        <a href="#features">FEATURES</a>
        <a href="#demo">DEMO</a>
        <a href="#about-us">ABOUT US</a>
    </div>
        <div class="nav-links">
            <a href="#hero" class="active">HOME</a>
            <a href="#features">FEATURES</a>
            <a href="#demo">DEMO</a>
            <a href="#about-us">ABOUT US</a>
        </div>
    </nav>
    </header>
    
    <main>
        <section id="hero" class="hero">
            <div class="hero-content">
                <h1>Navigate Your Budgeting Journey</h1>
                <p>Take control of your finances with our interactive planner. Start your path to financial freedom today!</p>
                <a href="login.php" class="cta-button">Start Budgeting Now</a>
            </div>
        </section>

        <section id="features" class="features">
            <h2>Why Choose Financial Compass?</h2>
            <div class="feature-grid">
                <div class="feature-card fade-in">
                    <h3>Interactive Planning</h3>
                    <p>Visualize your financial goals and track your progress with our intuitive interface.</p>
                </div>
                <div class="feature-card fade-in">
                    <h3>Smart Insights</h3>
                    <p>Receive personalized recommendations to optimize your budget and savings.</p>
                </div>
                <div class="feature-card fade-in">
                    <h3>Secure &amp; Private</h3>
                    <p>Your financial data is encrypted and never shared. Your privacy is our top priority.</p>
                </div>
                <div class="feature-card fade-in">
                    <h3>Multi-platform Sync</h3>
                    <p>Access your budget planner from any device, anytime, anywhere.</p>
                </div>
            </div>
        </section>

        <section id="demo" class="interactive-demo">
            <h2>Try Our Budget Calculator</h2>
            <div class="budget-calculator">
                <input type="number" id="income" placeholder="Enter your monthly income">
                <input type="number" id="expenses" placeholder="Enter your monthly expenses">
                <button onclick="calculateBudget()">Calculate Budget</button>
                <div id="result"></div>
            </div>
        </section>
        <section id="about-us" class="about-us">
            <h1>The Team</h2>
            <hr>
            <div class="team">
                <div class="indteam">
                    <img src="profile_of_team/Daniel.jpg" alt="">
                    <h3>Daniel Catena</h3>
                    <p>Lorem ipsum dolor, sit amet consectetur adipisicing elit. Facere repellendus accusantium, magni eos dicta non quis tempora porro fuga! Nobis rem odio obcaecati quia laboriosam atque soluta nulla nisi sapiente.</p>
                </div>
                <div class="indteam">
                <img src="profile_of_team/Zey.jpg" alt="">
                    <h3>Zey</h3>
                    <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Repellat, consequuntur consectetur distinctio tempora excepturi odit recusandae delectus ratione aspernatur, deserunt labore culpa qui dignissimos laudantium minima sit molestiae sunt vitae.</p>
                </div><div class="indteam">
                <img src="profile_of_team/Ken.jpg" alt="">
                    <h3>Ken</h3>
                    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Expedita laborum dolores porro nam temporibus? Est voluptates assumenda molestiae voluptatibus voluptas facere. Quaerat, eveniet sit facilis distinctio architecto quis illo adipisci!</p>
                </div><div class="indteam">
                <img src="profile_of_team/Enrico.jpg" alt="">
                    <h3>Enrico</h3>
                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Illo officiis earum ab repellat fuga perferendis quos expedita atque reiciendis. Iure quis maxime praesentium ea ipsum iusto reiciendis natus, sapiente explicabo!</p>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2023 Financial Compass. All rights reserved.</p>
    </footer>

    <script>
// Function to update active link based on scroll position
function setActiveLink() {
    const sections = document.querySelectorAll('section');
    const navLinks = document.querySelectorAll('.nav-links a');
    
    let currentSection = "";

    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        const sectionHeight = section.offsetHeight;
        const scrollPosition = window.scrollY + 100; // Add a little offset to detect sections early

        if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
            currentSection = section.getAttribute('id');
        }
    });

    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href').includes(currentSection)) {
            link.classList.add('active');
        }
    });
}

// Update the active link when clicking
document.querySelectorAll('.nav-links a').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault(); // Prevent default anchor behavior
        const targetId = this.getAttribute('href').substring(1); // Get the target section ID
        document.querySelector(`#${targetId}`).scrollIntoView({
            behavior: 'smooth'
        });

        // Manually set the active link
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.classList.remove('active');
        });
        this.classList.add('active');
    });
});

// Set active link on page load and scroll
window.addEventListener('scroll', setActiveLink);
window.addEventListener('DOMContentLoaded', setActiveLink);
function calculateBudget() {
            const income = parseFloat(document.getElementById('income').value);
            const expenses = parseFloat(document.getElementById('expenses').value);
            
            if (isNaN(income) || isNaN(expenses)) {
                document.getElementById('result').textContent = 'Please enter valid numbers for income and expenses.';
                return;
            }
            
            const surplus = income - expenses;
            let message = '';
            
            if (surplus > 0) {
                message = `Great job! You have a surplus of ₱${surplus.toFixed(2)}. Consider saving or investing this amount.`;
            } else if (surplus < 0) {
                message = `You're overspending by ₱${Math.abs(surplus).toFixed(2)}. Try to reduce your expenses or increase your income.`;
            } else {
                message = "You're breaking even. Consider finding ways to increase your income or reduce expenses to start saving.";
            }
            
            document.getElementById('result').textContent = message;
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
    const elements = document.querySelectorAll('.fade-in');

    const observer = new IntersectionObserver(
        (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    observer.unobserve(entry.target); // Stop observing once it has been animated
                }
            });
        },
        {
            threshold: 0.1, // Adjusts how much of the element is visible before triggering
        }
    );

    elements.forEach(element => {
        observer.observe(element);
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const fadeElements = document.querySelectorAll('.fade-in');

    // Create an intersection observer to detect when elements are in view
    const observer = new IntersectionObserver(
        (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Add the class 'in-view' when the element is in the viewport
                    entry.target.classList.add('in-view');
                    observer.unobserve(entry.target); // Stop observing once it has been animated
                }
            });
        },
        {
            threshold: 0.1, // Adjust visibility to trigger when 10% of the element is visible
        }
    );

    // Observe each fade-in element
    fadeElements.forEach(element => {
        observer.observe(element);
    });
});
document.addEventListener('DOMContentLoaded', function () {
        const hamburger = document.querySelector('.hamburger');
        const sidebar = document.querySelector('.sidebar');

        hamburger.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    });
    </script>
</body>
</html>