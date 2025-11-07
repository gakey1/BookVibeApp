    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <!-- About Column -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>About BookVibe</h5>
                    <p class="text-light">Your ultimate destination for discovering, reviewing, and collecting your favorite books in one beautiful platform.</p>
                    <div class="social-icons">
                        <a href="https://twitter.com/bookvibe" target="_blank" rel="noopener"><i class="fab fa-twitter"></i></a>
                        <a href="https://facebook.com/bookvibe" target="_blank" rel="noopener"><i class="fab fa-facebook"></i></a>
                        <a href="https://instagram.com/bookvibe" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
                        <a href="https://linkedin.com/company/bookvibe" target="_blank" rel="noopener"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                
                <!-- Explore Column -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Explore</h5>
                    <ul class="list-unstyled">
                        <li><a href="browse.php">Browse Books</a></li>
                        <li><a href="browse.php?sort=newest">New Releases</a></li>
                        <li><a href="browse.php?sort=popular">Bestsellers</a></li>
                        <li><a href="browse.php">All Genres</a></li>
                    </ul>
                </div>
                
                <!-- Community Column -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Community</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" onclick="showComingSoon(event)">Reviews</a></li>
                        <li><a href="#" onclick="showComingSoon(event)">Book Clubs</a></li>
                        <li><a href="#" onclick="showComingSoon(event)">Author Interviews</a></li>
                        <li><a href="#" onclick="showComingSoon(event)">Reading Challenges</a></li>
                    </ul>
                </div>
                
                <!-- Support Column -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Support</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" onclick="showComingSoon(event)">Help Center</a></li>
                        <li><a href="#" onclick="showComingSoon(event)">Contact Us</a></li>
                        <li><a href="#" onclick="showComingSoon(event)">Privacy Policy</a></li>
                        <li><a href="#" onclick="showComingSoon(event)">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Bottom Bar -->
            <hr class="border-secondary">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="text-light mb-0">© 2025 BookVibe. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- jQuery Library -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- API JavaScript -->
    <script src="assets/js/api.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="assets/js/script.js"></script>
    
    <!-- Coming Soon Modal -->
    <div class="modal fade" id="comingSoonModal" tabindex="-1" aria-labelledby="comingSoonModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="comingSoonModalLabel" style="color: var(--primary-purple);">Coming Soon!</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-rocket fa-3x mb-3" style="color: var(--primary-purple);"></i>
                    <h4>Exciting Feature in Development</h4>
                    <p class="text-muted mb-4">This feature is currently being developed and will be available soon. Stay tuned for updates!</p>
                    <div class="d-flex justify-content-center gap-2">
                        <span class="badge bg-secondary">In Development</span>
                        <span class="badge" style="background: var(--primary-purple);">Coming Soon</span>
                    </div>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn" style="background: var(--primary-purple); color: white;" data-bs-dismiss="modal">Got it!</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function showComingSoon(event) {
        event.preventDefault();
        const modal = new bootstrap.Modal(document.getElementById('comingSoonModal'));
        modal.show();
    }
    </script>
</body>
</html>
