<?php
session_start();
include 'user_helper.php';

// Get user info if logged in
$userInitials = 'U';
$userDisplayName = 'User';
if (isset($_SESSION['user_id'])) {
    include 'config.php';
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT username FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username);
    if ($stmt->fetch()) {
        $userInitials = getUserInitials($username);
        $userDisplayName = getUserDisplayName($username);
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Live Tutorial - SignSpeak</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">
    <link rel="shortcut icon" href="img/logo-ss.png" type="image/x-icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/tutorial.css?v=<?php echo time(); ?>" rel="stylesheet">
    
    <!-- Custom Live Tutorial Styles -->
    <style>
        .live-tutorial-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .word-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .word-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }
        
        .word-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: var(--primary);
        }
        
        .word-card.selected {
            background: linear-gradient(135deg, var(--primary), #05a3b1);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(6, 187, 204, 0.3);
        }
        
        .word-card .word-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .word-card .word-category {
            font-size: 12px;
            opacity: 0.7;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .category-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
            justify-content: center;
        }
        
        .category-btn {
            padding: 8px 20px;
            border: 2px solid var(--primary);
            background: white;
            color: var(--primary);
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .category-btn:hover,
        .category-btn.active {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(6, 187, 204, 0.3);
        }
        
        .search-container {
            margin-bottom: 30px;
        }
        
        .search-input {
            width: 100%;
            max-width: 500px;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(6, 187, 204, 0.1);
        }
        
        .selected-words {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .selected-words h4 {
            color: var(--primary);
            margin-bottom: 15px;
        }
        
        .selected-word-tag {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            margin: 5px;
            font-size: 14px;
            position: relative;
        }
        
        .selected-word-tag .remove-btn {
            margin-left: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .start-tutorial-btn {
            background: linear-gradient(135deg, var(--primary), #05a3b1);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 25px;
            font-size: 18px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(6, 187, 204, 0.3);
        }
        
        .start-tutorial-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(6, 187, 204, 0.4);
        }
        
        .start-tutorial-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .word-count {
            text-align: center;
            margin: 20px 0;
            font-size: 16px;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .word-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 10px;
            }
            
            .word-card {
                padding: 15px;
            }
            
            .word-card .word-title {
                font-size: 16px;
            }
            
            .category-filter {
                justify-content: flex-start;
                overflow-x: auto;
                padding-bottom: 10px;
            }
            
            .category-btn {
                white-space: nowrap;
                flex-shrink: 0;
            }
        }
    </style>
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <!-- Navbar Start -->
    <nav class="navbar navbar-expand-lg bg-white navbar-light shadow sticky-top p-0">
        <a href="index.php" class="navbar-brand d-flex align-items-center px-4 px-lg-5">
            <img src="img/logo-ss.png?v=<?php echo time(); ?>" alt="SignSpeak" style="height:36px; width:auto; display:inline-block;" class="me-2" onerror="this.src='img/logo-ss.PNG';this.onerror=null;">
            <h2 class="m-0 text-primary">SignSpeak</h2>
        </a>
        <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto p-4 p-lg-0">
                <a href="index.php" class="nav-item nav-link">Home</a>
                <a href="tutorial.php" class="nav-item nav-link">Tutorial</a>
                <a href="about.php" class="nav-item nav-link">About Us</a>
                <a href="progress.php" class="nav-item nav-link progress-btn">
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="user-initials me-2"><?php echo $userInitials; ?></span>
                    <?php else: ?>
                    <i class="fa-solid fa-user fa-lg me-2"></i>
                    <?php endif; ?>
                    <span class="progress-text">Progress</span></a>     
           </div>
        </div>
    </nav>
    <!-- Navbar End -->

    <!-- Live Tutorial Container -->
    <div class="live-tutorial-container">
        <div class="container py-5">
            <!-- Header Section -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-primary mb-3">Live Tutorial</h1>
                <p class="lead text-muted">Select the sign language words you want to practice and start your interactive learning session!</p>
            </div>

            <!-- Search and Filter Section -->
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Search Bar -->
                    <div class="search-container text-center">
                        <input type="text" id="searchInput" class="search-input" placeholder="Search for words...">
                    </div>

                    <!-- Category Filters -->
                    <div class="category-filter">
                        <button class="category-btn active" data-category="all">All Words</button>
                        <button class="category-btn" data-category="numbers">Numbers</button>
                        <button class="category-btn" data-category="alphabet">Alphabet</button>
                        <button class="category-btn" data-category="greetings">Greetings</button>
                        <button class="category-btn" data-category="verbs">Common Verbs</button>
                        <button class="category-btn" data-category="nouns">Nouns</button>
                        <button class="category-btn" data-category="adjectives">Adjectives</button>
                        <button class="category-btn" data-category="questions">Questions</button>
                    </div>

                    <!-- Word Count -->
                    <div class="word-count">
                        <span id="wordCount">0</span> words selected
                    </div>
                </div>
            </div>

            <!-- Selected Words Display -->
            <div class="row justify-content-center" id="selectedWordsContainer" style="display: none;">
                <div class="col-lg-10">
                    <div class="selected-words">
                        <h4><i class="fas fa-check-circle me-2"></i>Selected Words</h4>
                        <div id="selectedWordsList"></div>
                        <div class="text-center mt-3">
                            <button class="start-tutorial-btn" id="startTutorialBtn" disabled>
                                <i class="fas fa-play me-2"></i>Start Live Tutorial
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Words Grid -->
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="word-grid" id="wordsGrid">
                        <!-- Words will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Tutorial Button -->
    <a href="tutorial.php" class="btn btn-lg btn-secondary btn-lg-square back-to-tutorial-btn">
        <i class="fas fa-arrow-left"></i>
        <span class="btn-text">Back to Tutorial</span>
    </a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <!-- Live Tutorial JavaScript -->
    <script>
        // Sign language words data (61 words total)
        const signWords = {
            // Numbers (10)
            "0": { category: "numbers", title: "Zero", description: "The sign for 0 is formed by bringing all fingers together to form a circle." },
            "1": { category: "numbers", title: "One", description: "To sign 1, hold up your index finger. Palm facing forward." },
            "2": { category: "numbers", title: "Two", description: "To sign 2, hold up your index and middle fingers." },
            "3": { category: "numbers", title: "Three", description: "To sign 3, hold up your thumb, index, and middle fingers." },
            "4": { category: "numbers", title: "Four", description: "To sign 4, hold up your thumb, index, middle, and ring fingers." },
            "5": { category: "numbers", title: "Five", description: "To sign 5, hold up all fingers with the palm facing forward." },
            "6": { category: "numbers", title: "Six", description: "To sign 6, hold up your thumb and pinky finger." },
            "7": { category: "numbers", title: "Seven", description: "To sign 7, hold up your thumb and index finger." },
            "8": { category: "numbers", title: "Eight", description: "To sign 8, hold up your thumb, index, and middle fingers." },
            "9": { category: "numbers", title: "Nine", description: "To sign 9, hold up your thumb, index, middle, and ring fingers." },

            // Alphabet (26)
            "A": { category: "alphabet", title: "A", description: "The sign for A is made by forming a fist with your thumb resting on the side." },
            "B": { category: "alphabet", title: "B", description: "The sign for B is made by holding your fingers together and raising your palm." },
            "C": { category: "alphabet", title: "C", description: "The sign for C is made by forming a 'C' shape with your hand." },
            "D": { category: "alphabet", title: "D", description: "The sign for D is made by holding up your index finger while the other fingers are folded down." },
            "E": { category: "alphabet", title: "E", description: "The sign for E is made by holding your fingers together and bending them at the knuckles." },
            "F": { category: "alphabet", title: "F", description: "The sign for F is made by forming a circle with your thumb and index finger." },
            "G": { category: "alphabet", title: "G", description: "The sign for G is made by holding your index finger and thumb parallel." },
            "H": { category: "alphabet", title: "H", description: "The sign for H is made by holding up your index and middle fingers together." },
            "I": { category: "alphabet", title: "I", description: "The sign for I is made by holding up your pinky finger." },
            "J": { category: "alphabet", title: "J", description: "The sign for J is made by drawing a 'J' in the air with your pinky finger." },
            "K": { category: "alphabet", title: "K", description: "The sign for K is made by holding up your index and middle fingers in a V shape." },
            "L": { category: "alphabet", title: "L", description: "The sign for L is made by forming an L shape with your thumb and index finger." },
            "M": { category: "alphabet", title: "M", description: "The sign for M is made by placing your thumb under your three folded fingers." },
            "N": { category: "alphabet", title: "N", description: "The sign for N is made by placing your thumb under two folded fingers." },
            "O": { category: "alphabet", title: "O", description: "The sign for O is made by forming a circle with your fingers." },
            "P": { category: "alphabet", title: "P", description: "The sign for P is made by holding your index and middle fingers down while your thumb is extended." },
            "Q": { category: "alphabet", title: "Q", description: "The sign for Q is made by holding your index finger down while your thumb is extended." },
            "R": { category: "alphabet", title: "R", description: "The sign for R is made by crossing your index and middle fingers." },
            "S": { category: "alphabet", title: "S", description: "The sign for S is made by forming a fist with your thumb on top." },
            "T": { category: "alphabet", title: "T", description: "The sign for T is made by forming a fist with your thumb resting on the side." },
            "U": { category: "alphabet", title: "U", description: "The sign for U is made by holding up your index and middle fingers together." },
            "V": { category: "alphabet", title: "V", description: "The sign for V is made by holding up your index and middle fingers in a V shape." },
            "W": { category: "alphabet", title: "W", description: "The sign for W is made by holding up your index, middle, and ring fingers." },
            "X": { category: "alphabet", title: "X", description: "The sign for X is made by holding up your index finger and curling the other fingers." },
            "Y": { category: "alphabet", title: "Y", description: "The sign for Y is made by holding up your thumb and pinky finger." },
            "Z": { category: "alphabet", title: "Z", description: "The sign for Z is made by drawing a 'Z' in the air with your index finger." },

            // Greetings (5)
            "Hello": { category: "greetings", title: "Hello", description: "The sign for Hello is made by waving your hand." },
            "Goodbye": { category: "greetings", title: "Goodbye", description: "To sign 'Goodbye' in ASL, raise your right hand with fingers together, palm outward, and wave it from side to side." },
            "Thank you": { category: "greetings", title: "Thank you", description: "To sign 'Thank you' in ASL, extend your fingers and bring your hand from your chin outward." },
            "Please": { category: "greetings", title: "Please", description: "To sign 'Please' in ASL, place your flat hand on your chest and move it in a circular motion." },
            "Sorry": { category: "greetings", title: "Sorry", description: "To sign 'Sorry' in ASL, make a fist and rub it in a circular motion on your chest." },

            // Common Verbs (5)
            "Eat": { category: "verbs", title: "Eat", description: "To sign 'Eat' in ASL, bring your dominant hand to your mouth as if holding food." },
            "Drink": { category: "verbs", title: "Drink", description: "To sign 'Drink' in ASL, mimic holding a cup and bring it to your mouth." },
            "Go": { category: "verbs", title: "Go", description: "To sign 'Go' in ASL, extend your arm forward with your palm facing down and move it away from you." },
            "Help": { category: "verbs", title: "Help", description: "To sign 'Help' in ASL, place your dominant hand on top of your non-dominant hand and move them slightly upward." },
            "Stop": { category: "verbs", title: "Stop", description: "To sign 'Stop' in ASL, hold your dominant hand up with fingers extended and palm facing out." },

            // Nouns (5)
            "Home": { category: "nouns", title: "Home", description: "To sign 'Home' in ASL, place your dominant hand near your cheek and move it away." },
            "Water": { category: "nouns", title: "Water", description: "To sign 'Water' in ASL, mimic holding a cup and bring it to your mouth." },
            "Friend": { category: "nouns", title: "Friend", description: "To sign 'Friend' in ASL, interlock your index fingers and move them in a small circle." },
            "Teacher": { category: "nouns", title: "Teacher", description: "To sign 'Teacher' in ASL, place your hands together and move them in a teaching motion." },
            "Book": { category: "nouns", title: "Book", description: "To sign 'Book' in ASL, place your hands together and open them like opening a book." },

            // Adjectives (5)
            "Big": { category: "adjectives", title: "Big", description: "To sign 'Big' in ASL, hold your hands apart with palms facing each other and move them outward." },
            "Small": { category: "adjectives", title: "Small", description: "To sign 'Small' in ASL, bring your hands close together with palms facing each other." },
            "Happy": { category: "adjectives", title: "Happy", description: "To sign 'Happy' in ASL, place your hands on your chest and move them upward with a smiling expression." },
            "Sad": { category: "adjectives", title: "Sad", description: "To sign 'Sad' in ASL, place your hands on your face and move them downward with a sad expression." },
            "Good": { category: "adjectives", title: "Good", description: "To sign 'Good' in ASL, place your dominant hand on your chin and move it forward." },

            // Questions (6)
            "Who?": { category: "questions", title: "Who?", description: "To sign 'Who?' in ASL, hold your hand up with your index finger extended and move it in a small circle near your mouth." },
            "What?": { category: "questions", title: "What?", description: "To sign 'What?' in ASL, hold both hands up with palms facing up and move them in a questioning motion." },
            "Where?": { category: "questions", title: "Where?", description: "To sign 'Where?' in ASL, hold your index finger up and move it in a circular motion while looking around." },
            "When?": { category: "questions", title: "When?", description: "To sign 'When?' in ASL, hold your index finger up and move it in a back-and-forth motion near your temple." },
            "Why?": { category: "questions", title: "Why?", description: "To sign 'Why?' in ASL, place your hand on your forehead and move it forward while making a questioning expression." },
            "How?": { category: "questions", title: "How?", description: "To sign 'How?' in ASL, hold both hands up with palms facing each other and move them in a circular motion." }
        };

        let selectedWords = new Set();
        let currentFilter = 'all';
        let currentSearch = '';

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            renderWords();
            setupEventListeners();
        });

        function setupEventListeners() {
            // Category filter buttons
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    currentFilter = this.dataset.category;
                    renderWords();
                });
            });

            // Search input
            document.getElementById('searchInput').addEventListener('input', function() {
                currentSearch = this.value.toLowerCase();
                renderWords();
            });

            // Start tutorial button
            document.getElementById('startTutorialBtn').addEventListener('click', function() {
                if (selectedWords.size > 0) {
                    // Store selected words in sessionStorage for the tutorial
                    sessionStorage.setItem('selectedWords', JSON.stringify(Array.from(selectedWords)));
                    // Redirect to live practice page with selected words
                    window.location.href = 'live_practice.php';
                }
            });
        }

        function renderWords() {
            const wordsGrid = document.getElementById('wordsGrid');
            wordsGrid.innerHTML = '';

            Object.entries(signWords).forEach(([key, word]) => {
                // Apply filters
                if (currentFilter !== 'all' && word.category !== currentFilter) return;
                if (currentSearch && !word.title.toLowerCase().includes(currentSearch)) return;

                const wordCard = document.createElement('div');
                wordCard.className = `word-card ${selectedWords.has(key) ? 'selected' : ''}`;
                wordCard.innerHTML = `
                    <div class="word-title">${word.title}</div>
                    <div class="word-category">${word.category}</div>
                `;
                
                wordCard.addEventListener('click', function() {
                    toggleWordSelection(key);
                });

                wordsGrid.appendChild(wordCard);
            });
        }

        function toggleWordSelection(wordKey) {
            if (selectedWords.has(wordKey)) {
                selectedWords.delete(wordKey);
            } else {
                selectedWords.add(wordKey);
            }
            
            renderWords();
            updateSelectedWordsDisplay();
        }

        function updateSelectedWordsDisplay() {
            const container = document.getElementById('selectedWordsContainer');
            const list = document.getElementById('selectedWordsList');
            const count = document.getElementById('wordCount');
            const startBtn = document.getElementById('startTutorialBtn');

            count.textContent = selectedWords.size;

            if (selectedWords.size > 0) {
                container.style.display = 'block';
                list.innerHTML = '';
                
                selectedWords.forEach(wordKey => {
                    const word = signWords[wordKey];
                    const tag = document.createElement('span');
                    tag.className = 'selected-word-tag';
                    tag.innerHTML = `
                        ${word.title}
                        <span class="remove-btn" onclick="removeWord('${wordKey}')">&times;</span>
                    `;
                    list.appendChild(tag);
                });

                startBtn.disabled = false;
            } else {
                container.style.display = 'none';
                startBtn.disabled = true;
            }
        }

        function removeWord(wordKey) {
            selectedWords.delete(wordKey);
            renderWords();
            updateSelectedWordsDisplay();
        }

        // Make removeWord globally available
        window.removeWord = removeWord;
    </script>

    <!-- User Presence Tracking -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <script src="js/user-presence.js"></script>
    <script>
        // Set the current user ID for the presence manager
        window.currentUserId = <?php echo $_SESSION['user_id']; ?>;
    </script>
    <?php endif; ?>
</body>
</html>
