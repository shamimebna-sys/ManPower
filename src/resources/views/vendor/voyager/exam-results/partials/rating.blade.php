
<input type="number" min="0" max="5">

{{--@json($row)--}}
{{--<div class="star-rating">--}}
{{--    <span class="star icon voyager-star"></span>--}}
{{--    <span class="star icon voyager-star"></span>--}}
{{--    <span class="star icon voyager-star"></span>--}}
{{--    <span class="star icon voyager-star"></span>--}}
{{--    <span class="star icon voyager-star"></span>--}}
{{--    <input type="text" class="rating-value" value="0">--}}
{{--</div>--}}

<style>
    .star-rating .star {
        cursor: pointer;
        font-size: 1em;
    }
    .star-rating .star.filled {
        color: green;
    }
</style>

<script>
    const starElements = document.querySelectorAll('.star');
    const ratingValue = document.querySelector('.rating-value');
    let rating = 0;

    starElements.forEach((star, index) => {
        star.addEventListener('click', () => {
            rating = index + 1;
            updateRating();
        });
    });

    function updateRating() {
        starElements.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('voyager-star');
                star.classList.add('voyager-star-two');
                star.classList.add('filled');
            } else {
                star.classList.remove('voyager-star-two');
                star.classList.remove('filled');
                star.classList.add('voyager-star');
            }
        });
        ratingValue.val = `${rating}/5`;
    }
</script>
