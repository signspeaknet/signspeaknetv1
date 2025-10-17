// <!-- This script is for the tutorial page of the ASL app. It handles sidebar toggling, dropdowns, and GIF display logic. -->

// Global variables
const gifDisplay = document.getElementById('gifDisplay');
const tutorialText = document.getElementById('tutorialText');
const gifControls = document.getElementById('gifControls');
let currentGifIndex = null;
let lastClickedLink = null;

// Tutorial GIF data with descriptions
const gifData = {
    // Numbers
    "0": { src: "img/tutorialgif/0.gif", title: "Number: Zero (0)", description: "The sign for 0 is formed by bringing all fingers together to form a circle." },
    "1": { src: "img/tutorialgif/1.gif", title: "Number: One (1)", description: "To sign 1, hold up your index finger. Palm facing forward." },
    "2": { src: "img/tutorialgif/2.gif", title: "Number: Two (2)", description: "To sign 2, hold up your index and middle fingers." },
    "3": { src: "img/tutorialgif/3.gif", title: "Number: Three (3)", description: "To sign 3, hold up your thumb, index, and middle fingers." },
    "4": { src: "img/tutorialgif/4.gif", title: "Number: Four (4)", description: "To sign 4, hold up your thumb, index, middle, and ring fingers." },
    "5": { src: "img/tutorialgif/5.gif", title: "Number: Five (5)", description: "To sign 5, hold up all fingers with the palm facing forward." },
    "6": { src: "img/tutorialgif/6.gif", title: "Number: Six (6)", description: "To sign 6, hold up your thumb and pinky finger." },
    "7": { src: "img/tutorialgif/7.gif", title: "Number: Seven (7)", description: "To sign 7, hold up your thumb and index finger." },
    "8": { src: "img/tutorialgif/8.gif", title: "Number: Eight (8)", description: "To sign 8, hold up your thumb, index, and middle fingers." },
    "9": { src: "img/tutorialgif/9.gif", title: "Number: Nine (9)", description: "To sign 9, hold up your thumb, index, middle, and ring fingers." },

    // Alphabet
    "A": { src: "img/tutorialgif/A.gif", title: "Letter: A", description: "The sign for A is made by forming a fist with your thumb resting on the side." },
    "B": { src: "img/tutorialgif/B.gif", title: "Letter: B", description: "The sign for B is made by holding your fingers together and raising your palm." },
    "C": { src: "img/tutorialgif/C.gif", title: "Letter: C", description: "The sign for C is made by forming a 'C' shape with your hand." },
    "D": { src: "img/tutorialgif/D.gif", title: "Letter: D", description: "The sign for D is made by holding up your index finger while the other fingers are folded down." },
    "E": { src: "img/tutorialgif/E.gif", title: "Letter: E", description: "The sign for E is made by holding your fingers together and bending them at the knuckles." },
    "F": { src: "img/tutorialgif/F.gif", title: "Letter: F", description: "The sign for F is made by forming a circle with your thumb and index finger." },
    "G": { src: "img/tutorialgif/G.gif", title: "Letter: G", description: "The sign for G is made by holding your index finger and thumb parallel." },
    "H": { src: "img/tutorialgif/H.gif", title: "Letter: H", description: "The sign for H is made by holding up your index and middle fingers together." },
    "I": { src: "img/tutorialgif/I.gif", title: "Letter: I", description: "The sign for I is made by holding up your pinky finger." },
    "J": { src: "img/tutorialgif/J.gif", title: "Letter: J", description: "The sign for J is made by drawing a 'J' in the air with your pinky finger." },
    "K": { src: "img/tutorialgif/K.gif", title: "Letter: K", description: "The sign for K is made by holding up your index and middle fingers in a V shape." },
    "L": { src: "img/tutorialgif/L.gif", title: "Letter: L", description: "The sign for L is made by forming an L shape with your thumb and index finger." },
    "M": { src: "img/tutorialgif/M.gif", title: "Letter: M", description: "The sign for M is made by placing your thumb under your three folded fingers." },
    "N": { src: "img/tutorialgif/N.gif", title: "Letter: N", description: "The sign for N is made by placing your thumb under two folded fingers." },
    "O": { src: "img/tutorialgif/O.gif", title: "Letter: O", description: "The sign for O is made by forming a circle with your fingers." },
    "P": { src: "img/tutorialgif/P.gif", title: "Letter: P", description: "The sign for P is made by holding your index and middle fingers down while your thumb is extended." },
    "Q": { src: "img/tutorialgif/Q.gif", title: "Letter: Q", description: "The sign for Q is made by holding your index finger down while your thumb is extended." },
    "R": { src: "img/tutorialgif/R.gif", title: "Letter: R", description: "The sign for R is made by crossing your index and middle fingers." },
    "S": { src: "img/tutorialgif/S.gif", title: "Letter: S", description: "The sign for S is made by forming a fist with your thumb on top." },
    "T": { src: "img/tutorialgif/T.gif", title: "Letter: T", description: "The sign for T is made by forming a fist with your thumb resting on the side." },
    "U": { src: "img/tutorialgif/U.gif", title: "Letter: U", description: "The sign for U is made by holding up your index and middle fingers together." },
    "V": { src: "img/tutorialgif/V.gif", title: "Letter: V", description: "The sign for V is made by holding up your index and middle fingers in a V shape." },
    "W": { src: "img/tutorialgif/W.gif", title: "Letter: W", description: "The sign for W is made by holding up your index, middle, and ring fingers." },
    "X": { src: "img/tutorialgif/X.gif", title: "Letter: X", description: "The sign for X is made by holding up your index finger and curling the other fingers." },
    "Y": { src: "img/tutorialgif/Y.gif", title: "Letter: Y", description: "The sign for Y is made by holding up your thumb and pinky finger." },
    "Z": { src: "img/tutorialgif/Z.gif", title: "Letter: Z", description: "The sign for Z is made by drawing a 'Z' in the air with your index finger." },

    // Greetings
    "Hello": { src: "img/tutorialgif/hello.gif", title: "Greetings: Hello", description: "The sign for Hello is made by waving your hand." },
    "Goodbye": { src: "img/tutorialgif/goodbye.gif", title: "Greetings: Goodbye", description: "To sign 'Goodbye' in ASL, raise your right hand with fingers together, palm outward, and wave it from side to side." },
    "Thank you": { src: "img/tutorialgif/thankyou.gif", title: "Greetings: Thank you", description: "To sign 'Thank you' in ASL, extend your fingers and bring your hand from your chin outward." },
    "Please": { src: "img/tutorialgif/please.gif", title: "Greetings: Please", description: "To sign 'Please' in ASL, place your flat hand on your chest and move it in a circular motion." },
    "Sorry": { src: "img/tutorialgif/sorry.gif", title: "Greetings: Sorry", description: "To sign 'Sorry' in ASL, make a fist and rub it in a circular motion on your chest." },

    // Common Verbs
    "Eat": { src: "img/tutorialgif/eat.gif", title: "Common Verbs: Eat", description: "To sign 'Eat' in ASL, bring your dominant hand to your mouth as if holding food." },
    "Drink": { src: "img/tutorialgif/drink.gif", title: "Common Verbs: Drink", description: "To sign 'Drink' in ASL, mimic holding a cup and bring it to your mouth." },
    "Go": { src: "img/tutorialgif/go.gif", title: "Common Verbs: Go", description: "To sign 'Go' in ASL, extend your arm forward with your palm facing down and move it away from you." },
    "Help": { src: "img/tutorialgif/help.gif", title: "Common Verbs: Help", description: "To sign 'Help' in ASL, place your dominant hand on top of your non-dominant hand and move them slightly upward." },
    "Stop": { src: "img/tutorialgif/stop.gif", title: "Common Verbs: Stop", description: "To sign 'Stop' in ASL, hold your dominant hand up with fingers extended and palm facing out." },

    // Nouns
    "Home": { src: "img/tutorialgif/home.gif", title: "Nouns: Home", description: "To sign 'Home' in ASL, place your dominant hand near your cheek and move it away." },
    "Water": { src: "img/tutorialgif/water.gif", title: "Nouns: Water", description: "To sign 'Water' in ASL, mimic holding a cup and bring it to your mouth." },
    "Friend": { src: "img/tutorialgif/friend.gif", title: "Nouns: Friend", description: "To sign 'Friend' in ASL, interlock your index fingers and move them in a small circle." },
    "Teacher": { src: "img/tutorialgif/teacher.gif", title: "Nouns: Teacher", description: "To sign 'Teacher' in ASL, place your hands together and move them in a teaching motion." },
    "Book": { src: "img/tutorialgif/book.gif", title: "Nouns: Book", description: "To sign 'Book' in ASL, place your hands together and open them like opening a book." },

    // Adjectives
    "Big": { src: "img/tutorialgif/big.gif", title: "Adjectives: Big", description: "To sign 'Big' in ASL, hold your hands apart with palms facing each other and move them outward." },
    "Small": { src: "img/tutorialgif/small.gif", title: "Adjectives: Small", description: "To sign 'Small' in ASL, bring your hands close together with palms facing each other." },
    "Happy": { src: "img/tutorialgif/happy.gif", title: "Adjectives: Happy", description: "To sign 'Happy' in ASL, place your hands on your chest and move them upward with a smiling expression." },
    "Sad": { src: "img/tutorialgif/sad.gif", title: "Adjectives: Sad", description: "To sign 'Sad' in ASL, place your hands on your face and move them downward with a sad expression." },
    "Good": { src: "img/tutorialgif/good.gif", title: "Adjectives: Good", description: "To sign 'Good' in ASL, place your dominant hand on your chin and move it forward." },

    // Questions
    "Who?": { src: "img/tutorialgif/who.gif", title: "Questions: Who?", description: "To sign 'Who?' in ASL, hold your hand up with your index finger extended and move it in a small circle near your mouth." },
    "What?": { src: "img/tutorialgif/what.gif", title: "Questions: What?", description: "To sign 'What?' in ASL, hold both hands up with palms facing up and move them in a questioning motion." },
    "Where?": { src: "img/tutorialgif/where.gif", title: "Questions: Where?", description: "To sign 'Where?' in ASL, hold your index finger up and move it in a circular motion while looking around." },
    "When?": { src: "img/tutorialgif/when.gif", title: "Questions: When?", description: "To sign 'When?' in ASL, hold your index finger up and move it in a back-and-forth motion near your temple." },
    "Why?": { src: "img/tutorialgif/why.gif", title: "Questions: Why?", description: "To sign 'Why?' in ASL, place your hand on your forehead and move it forward while making a questioning expression." },
    "How?": { src: "img/tutorialgif/how.gif", title: "Questions: How?", description: "To sign 'How?' in ASL, hold both hands up with palms facing each other and move them in a circular motion." }
};

// Sidebar Toggle with backdrop + scroll lock on mobile
const sidebarToggleBtn = document.getElementById('sidebarToggle');
const sidebarEl = document.getElementById('sidebar');
const sidebarBackdrop = document.getElementById('sidebarBackdrop');

function lockScroll() {
    document.body.style.overflow = 'hidden';
}
function unlockScroll() {
    document.body.style.overflow = '';
}

function openSidebar() {
    sidebarEl.classList.add('active');
    sidebarBackdrop.classList.add('show');
    lockScroll();
}

function closeSidebar() {
    sidebarEl.classList.remove('active');
    sidebarBackdrop.classList.remove('show');
    unlockScroll();
}

sidebarToggleBtn.addEventListener('click', function() {
    if (sidebarEl.classList.contains('active')) {
        closeSidebar();
    } else {
        openSidebar();
    }
});

sidebarBackdrop.addEventListener('click', closeSidebar);

// Dropdown toggle logic
document.querySelectorAll('.dropdown-toggle').forEach(button => {
    button.addEventListener('click', () => {
        const targetId = button.dataset.target;
        const targetMenu = document.querySelector(targetId);
        button.classList.toggle('active');
        targetMenu.classList.toggle('show');
    });
});

// Handle click on sidebar items
document.querySelectorAll('.small-dropdown').forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        const gifKey = link.getAttribute('data-gif-key');
        
        // Remove highlight from previous link
        if (lastClickedLink) {
            lastClickedLink.classList.remove('active-item');
        }
        
        // Add highlight to clicked link
        link.classList.add('active-item');
        lastClickedLink = link;
        
        if (gifKey && gifData[gifKey]) {
            currentGifIndex = gifKey;
            gifDisplay.src = gifData[gifKey].src;
            tutorialText.innerHTML = `<h2>${gifData[gifKey].title}</h2><p>${gifData[gifKey].description}</p>`;
            gifControls.style.display = "block";
            
            // If we're in a dropdown, make sure the parent category stays expanded
            const parentDropdown = link.closest('.collapse');
            if (parentDropdown) {
                parentDropdown.classList.add('show');
                const parentButton = document.querySelector(`[data-target="#${parentDropdown.id}"]`);
                if (parentButton) {
                    parentButton.classList.add('active');
                }
            }
        }

        // Auto-close sidebar on mobile after selecting an item
        if (window.innerWidth < 992) {
            closeSidebar();
        }
    });
});

// Handle Previous button click
document.getElementById('prevBtn').addEventListener('click', () => {
    const keys = Object.keys(gifData);
    const currentIndex = keys.indexOf(currentGifIndex);
    if (currentIndex > 0) {
        currentGifIndex = keys[currentIndex - 1];
        gifDisplay.src = gifData[currentGifIndex].src;
        tutorialText.innerHTML = `<h2>${gifData[currentGifIndex].title}</h2><p>${gifData[currentGifIndex].description}</p>`;
        
        // Update the highlighted item
        if (lastClickedLink) {
            lastClickedLink.classList.remove('active-item');
        }
        const newActiveLink = document.querySelector(`[data-gif-key="${currentGifIndex}"]`);
        if (newActiveLink) {
            newActiveLink.classList.add('active-item');
            lastClickedLink = newActiveLink;
            
            // Make sure the parent dropdown is expanded
            const parentDropdown = newActiveLink.closest('.collapse');
            if (parentDropdown) {
                // Hide all dropdowns first
                document.querySelectorAll('.collapse.show').forEach(dropdown => {
                    if (dropdown !== parentDropdown) {
                        dropdown.classList.remove('show');
                        const button = document.querySelector(`[data-target="#${dropdown.id}"]`);
                        if (button) button.classList.remove('active');
                    }
                });
                
                // Show the correct dropdown
                parentDropdown.classList.add('show');
                const parentButton = document.querySelector(`[data-target="#${parentDropdown.id}"]`);
                if (parentButton) {
                    parentButton.classList.add('active');
                }
            }
        }
    }
});

// Handle Next button click
document.getElementById('nextBtn').addEventListener('click', () => {
    const keys = Object.keys(gifData);
    const currentIndex = keys.indexOf(currentGifIndex);
    if (currentIndex < keys.length - 1) {
        currentGifIndex = keys[currentIndex + 1];
        gifDisplay.src = gifData[currentGifIndex].src;
        tutorialText.innerHTML = `<h2>${gifData[currentGifIndex].title}</h2><p>${gifData[currentGifIndex].description}</p>`;
        
        // Update the highlighted item
        if (lastClickedLink) {
            lastClickedLink.classList.remove('active-item');
        }
        const newActiveLink = document.querySelector(`[data-gif-key="${currentGifIndex}"]`);
        if (newActiveLink) {
            newActiveLink.classList.add('active-item');
            lastClickedLink = newActiveLink;
            
            // Make sure the parent dropdown is expanded
            const parentDropdown = newActiveLink.closest('.collapse');
            if (parentDropdown) {
                // Hide all dropdowns first
                document.querySelectorAll('.collapse.show').forEach(dropdown => {
                    if (dropdown !== parentDropdown) {
                        dropdown.classList.remove('show');
                        const button = document.querySelector(`[data-target="#${dropdown.id}"]`);
                        if (button) button.classList.remove('active');
                    }
                });
                
                // Show the correct dropdown
                parentDropdown.classList.add('show');
                const parentButton = document.querySelector(`[data-target="#${parentDropdown.id}"]`);
                if (parentButton) {
                    parentButton.classList.add('active');
                }
            }
        }
    }
});

// Privacy Policy Modal Handler
document.addEventListener('DOMContentLoaded', function() {
    // Get all Privacy Policy links
    const privacyLinks = document.querySelectorAll('a[href=""][class="btn btn-link"]');
    
    // Add click event listener to each link
    privacyLinks.forEach(link => {
        if (link.textContent.trim() === 'Privacy Policy') {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const modal = new bootstrap.Modal(document.getElementById('termsModal'));
                modal.show();
            });
        }
    });
});