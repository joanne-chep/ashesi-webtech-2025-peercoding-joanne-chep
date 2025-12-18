<?php
//Enable error reporting to help in debugging issues during development
error_reporting(E_ALL);
ini_set('display_errors', 1);//If it goes live change to 0

//Start the session to allow storage of user data across different pages
session_start();

//Include the external database connection file using require_once to ensure critical dependency
require_once "database.php";

//Check if the user is logged in. If not, redirect them to the login page to prevent unauthorized access
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

//We must block Therapists and Admins from booking sessions
//If the user is an admin, redirect them to their dashboard
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
        exit();
    }
    //If the user is a therapist, redirect them to their portal
    if ($_SESSION['role'] === 'therapist') {
        header("Location: landing_therapistpage.php");
        exit();
    }
}

//Get the logged-in user's ID from the session to use for booking
$user_id = $_SESSION['user_id'];
$message = "";

//Fetch verified therapists from the database to display them in the list
//We join the therapist_profiles table with serenity_users to get names and images of their profiles to be shown to the clients
$therapists = $conn->query("SELECT tp.*, u.full_name, u.profile_image FROM therapist_profiles tp JOIN serenity_users u ON tp.user_id = u.id WHERE tp.verification_status = 'approved'");

//Store all therapist data in an array to use in JavaScript later for filtering
$all_therapists = [];
while($row = $therapists->fetch_assoc()) { $all_therapists[] = $row; }

//Handle the form submission when a user books a session
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['therapist_id'])) {
    //Collect booking details from the form
    $tid = $_POST['therapist_id'];//The selected therapist's ID
    $date = $_POST['date'];//The chosen date
    $time = date("H:i", strtotime($_POST['time']));//Format the time correctly
    $type = $_POST['session_type'];//Adult or Child therapy
    $price = $_POST['price'];//The hourly rate

    //Prepare the SQL statement to insert the booking into the database
    //We set the initial status to 'Pending' so the therapist can approve it later
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, therapist_id, date, time, session_type, price, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("iisssd", $user_id, $tid, $date, $time, $type, $price);
    
    //Execute the query and show a success message if it works
    if ($stmt->execute()) $message = "Request sent! You can view status in your Profile.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Session | Serenity</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/base.css">
    <link rel="stylesheet" href="../CSS/booking.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include("Header.php"); ?>

    <div class="container">
        <?php if ($message): ?>
            <div style="background:#d1fae5; color:#065f46; padding:15px; border-radius:8px; margin-bottom:20px; text-align:center; border: 1px solid #bbf7d0;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="bookingForm">
            <input type="hidden" name="therapist_id" id="t_id">
            <input type="hidden" name="session_type" id="s_type">
            <input type="hidden" name="price" id="s_price">

            <div id="step1" class="step active">
                <h2 class="section-title">Who is this for?</h2>
                <p class="section-subtitle">Choose the type of support you need today.</p>
                
                <div class="category-grid">
                    <div class="cat-card" onclick="setCategory('Adult Therapy', 'adult')">
                        <div class="cat-img" style="background-image: url('https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?auto=format&fit=crop&w=500');"></div>
                        <div class="cat-body">
                            <h3>Adult (18+)</h3>
                            <p style="color:#65676B; font-size:14px;">Depression, Stress, Anxiety, Relationships</p>
                        </div>
                    </div>
                    <div class="cat-card" onclick="setCategory('Child/Teen Therapy', 'child')">
                        <div class="cat-img" style="background-image: url('https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?auto=format&fit=crop&w=500');"></div>
                        <div class="cat-body">
                            <h3>Child & Teen</h3>
                            <p style="color:#65676B; font-size:14px;">School issues, Behavior (Parent Consent Required)</p>
                        </div>
                    </div>
                </div>
            </div>

            <div id="step2" class="step">
                <button type="button" onclick="showStep('step1')" class="back-btn"><i class="fas fa-arrow-left" style="margin-right:8px;"></i> Back to Categories</button>
                <h2 class="section-title">Select a Professional</h2>
                <p class="section-subtitle">Browse our verified experts and view their profiles.</p>
                
                <div id="doc-list" class="therapist-grid"></div>
            </div>

            <div id="step3" class="step">
                <button type="button" onclick="showStep('step2')" class="back-btn"><i class="fas fa-arrow-left" style="margin-right:8px;"></i> Back to Therapists</button>
                
                <div class="confirm-box">
                    <h2 class="section-title" style="margin-bottom:20px;">Complete Booking</h2>
                    
                    <img id="confirm-img" src="" style="width:80px; height:80px; border-radius:50%; object-fit:cover; margin-bottom:10px;">
                    <h3 id="confirm-doc" style="margin:0; color:#050505;"></h3>
                    <p id="confirm-price" style="color:#4A7C59; font-weight:bold; margin-top:5px;"></p>
                    
                    <hr style="border:0; border-top:1px solid #eee; margin:20px 0;">
                    
                    <label style="display:block; text-align:left; font-weight:600; margin-bottom:5px;">Select Date</label>
                    <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
                    
                    <label style="display:block; text-align:left; font-weight:600; margin-bottom:5px;">Select Time</label>
                    <select name="time" required>
                        <option>09:00 AM</option><option>10:00 AM</option><option>11:00 AM</option>
                        <option>01:00 PM</option><option>02:00 PM</option><option>03:00 PM</option>
                        <option>04:00 PM</option><option>05:00 PM</option>
                    </select>
                    
                    <button class="btn-select" style="width:100%;">Confirm Request</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        //Pass the PHP therapist data to JavaScript
        const therapists = <?php echo json_encode($all_therapists); ?>;
        
        //Function to switch between the 3 steps
        function showStep(id) {
            document.querySelectorAll('.step').forEach(el => el.classList.remove('active'));
            document.getElementById(id).classList.add('active');
            window.scrollTo(0,0);
        }

        //Function to toggle the bio "Read More"
        function toggleBio(id, btn) {
            const bioText = document.getElementById(id);
            if (bioText.classList.contains('expanded')) {
                bioText.classList.remove('expanded');
                btn.innerText = "Read More";
            } else {
                bioText.classList.add('expanded');
                btn.innerText = "Show Less";
            }
        }

        //Function to filter therapists based on the category selected
        function setCategory(type, filter) {
            document.getElementById('s_type').value = type;
            const list = document.getElementById('doc-list');
            list.innerHTML = "";
            
            //Show child specialists only if 'child' is selected
            const docs = therapists.filter(t => {
                const spec = (t.specialization || "").toLowerCase();
                return filter === 'child' ? (spec.includes('child') || spec.includes('teen')) : (!spec.includes('child'));
            });

            //Show message if no doctors match
            if(docs.length === 0) {
                list.innerHTML = "<p style='color:#65676B; text-align:center; grid-column:1/-1; padding:40px;'>No specialists found for this category yet.</p>";
            }
            
            //Generate HTML cards for each therapist
            docs.forEach((t, index) => {
                const rawBio = t.bio || "No biography available.";
                const imgUrl = t.profile_image ? t.profile_image : "https://cdn-icons-png.flaticon.com/512/847/847969.png";
                
                //Only show "Read More" button if text is long (> 100 chars)
                let bioHtml = '';
                if (rawBio.length > 100) {
                    bioHtml = `
                        <p class="t-bio" id="bio-${index}">${rawBio}</p>
                        <button type="button" class="read-more-btn" onclick="toggleBio('bio-${index}', this)">Read More</button>
                    `;
                } else {
                    bioHtml = `<p class="t-bio">${rawBio}</p>`;
                }

                const div = document.createElement('div');
                div.className = 't-card';
                div.innerHTML = `
                    <img src="${imgUrl}" class="t-avatar">
                    <h3 class="t-name">Dr. ${t.full_name}</h3>
                    <div class="t-spec">${t.specialization}</div>
                    <div class="t-price">$${t.hourly_rate} / hr</div>
                    ${bioHtml}
                    <button type="button" class="btn-select">Select</button>
                `;
                
                //Clicking "Select" moves to the booking confirmation
                const selectBtn = div.querySelector('.btn-select');
                selectBtn.onclick = () => {
                    document.getElementById('t_id').value = t.user_id;
                    document.getElementById('s_price').value = t.hourly_rate;
                    document.getElementById('confirm-doc').innerText = "Dr. " + t.full_name;
                    document.getElementById('confirm-price').innerText = "$" + t.hourly_rate + " / session";
                    document.getElementById('confirm-img').src = imgUrl;
                    showStep('step3');
                };
                list.appendChild(div);
            });
            showStep('step2');
        }
    </script>

    <?php include("../HTML/footer.html"); ?>
</body>
</html>