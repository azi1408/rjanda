<?php
// This file contains the review modal HTML and JavaScript
?>
<style>
.star-rating {
    display: flex;
    gap: 10px;
    margin: 15px 0;
}

.star-rating input[type="radio"] {
    display: none;
}

.star-rating label {
    cursor: pointer;
    font-size: 30px;
    color: #ddd;
    transition: color 0.2s;
}

.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label {
    color: #ffd700;
}
</style>

<!-- Review Modal -->
<div id="reviewModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.6); z-index:9999; justify-content:center; align-items:center;">
    <div style="background:#fff; color:#000; padding:30px; border-radius:12px; max-width:500px; width:90%;">
        <h3 style="margin-top:0;">📝 Write a Review</h3>
        <form method="post" action="submit_review.php">
            <input type="hidden" name="order_id" id="reviewOrderId">
            
            <div class="star-rating">
                <input type="radio" name="rating" value="5" id="star5" required>
                <label for="star5">★</label>
                <input type="radio" name="rating" value="4" id="star4">
                <label for="star4">★</label>
                <input type="radio" name="rating" value="3" id="star3">
                <label for="star3">★</label>
                <input type="radio" name="rating" value="2" id="star2">
                <label for="star2">★</label>
                <input type="radio" name="rating" value="1" id="star1">
                <label for="star1">★</label>
            </div>

            <textarea name="comment" placeholder="Write your feedback..." rows="5" style="width:100%; border-radius:8px; padding:10px;" required></textarea>
            <br><br>
            <button type="submit" class="pay-btn">Submit Review</button>
            <button type="button" class="pay-btn cancel" onclick="closeReviewModal()">Cancel</button>
        </form>
    </div>
</div>

<script>
function initStarRating() {
    document.querySelectorAll('input[name="rating"]').forEach(input => {
        input.addEventListener('change', function() {
            const rating = this.value;
            const labels = document.querySelectorAll('label[for^="star"]');
            labels.forEach((label, index) => {
                label.style.color = index < rating ? '#ffd700' : '#ddd';
            });
        });
    });

    document.querySelectorAll('label[for^="star"]').forEach((label, index) => {
        label.addEventListener('mouseover', function() {
            const labels = document.querySelectorAll('label[for^="star"]');
            labels.forEach((l, i) => {
                l.style.color = i <= index ? '#ffd700' : '#ddd';
            });
        });

        label.addEventListener('mouseout', function() {
            const selectedRating = document.querySelector('input[name="rating"]:checked');
            const labels = document.querySelectorAll('label[for^="star"]');
            labels.forEach((l, i) => {
                l.style.color = selectedRating && i < selectedRating.value ? '#ffd700' : '#ddd';
            });
        });
    });
}

// Initialize star rating when the modal is opened
function openReviewModal(orderId) {
    document.getElementById('reviewOrderId').value = orderId;
    document.getElementById('reviewModal').style.display = 'flex';
    initStarRating();
}

function closeReviewModal() {
    document.getElementById('reviewModal').style.display = 'none';
}
</script> 