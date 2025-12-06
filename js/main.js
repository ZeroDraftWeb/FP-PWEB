// Main JavaScript file for GDD Organizer

// Function to load navigation dynamically
function loadNav() {
    // Determine the correct path based on the current page
    let navPath = 'nav.html'; // Default for root pages

    // Check if we're in the pages directory
    if (window.location.pathname.includes('/pages/')) {
        navPath = '../nav.html';
    }

    // Load navigation
    if (document.getElementById('navbar')) {
        fetch(navPath)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Navigation file not found at: ' + navPath);
                }
                return response.text();
            })
            .then(data => {
                // Process the navigation HTML to fix links based on current directory
                let navHTML = data;

                // Adjust paths if we're in the pages directory
                if (window.location.pathname.includes('/pages/')) {
                    // Remove the "pages/" prefix from internal links
                    navHTML = navHTML.replace(/href="pages\//g, 'href="');

                    // For links that are not absolute, external, or anchor links, add ../ to go up one directory
                    // First handle the specific case of index.html which should go up to root
                    navHTML = navHTML.replace(/href="(index\.html)"/g, 'href="../$1"');

                    // Then handle any other relative links (that don't start with # or external protocols)
                    navHTML = navHTML.replace(/href="(?!https?:\/\/|ftp:\/\/|\/\/|#)([^"]*\.html)"/g, 'href="../$1"');
                }

                document.getElementById('navbar').innerHTML = navHTML;

                // After loading nav, set active link
                setTimeout(setActiveNavLink, 100);
            })
            .catch(error => {
                console.error('Error loading navigation:', error);
                // Fallback: create a simple navigation
                let fallbackNav = `
                    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                        <div class="container-fluid">
                            <a class="navbar-brand" href="index.html">GDD Organizer</a>
                            <div class="navbar-nav">
                                <a class="nav-link" href="index.html">Home</a>
                                <a class="nav-link" href="pages/edit-project.html">Edit Project</a>
                                <a class="nav-link" href="pages/membership.html">Membership</a>
                                <a class="nav-link" href="pages/profile.html">Profile</a>
                            </div>
                        </div>
                    </nav>
                `;

                // Adjust paths if we're in the pages directory
                if (window.location.pathname.includes('/pages/')) {
                    fallbackNav = fallbackNav.replace(/href="pages\//g, 'href="');
                    fallbackNav = fallbackNav.replace(/href="(index\.html)"/g, 'href="../$1"');
                    fallbackNav = fallbackNav.replace(/href="(?!https?:\/\/|ftp:\/\/|\/\/|#)([^"]*\.html)"/g, 'href="../$1"');
                }

                document.getElementById('navbar').innerHTML = fallbackNav;
            });
    }
}

// Function to handle smooth scrolling for anchor links
function setupSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();

            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
}

// Function to set active navigation link based on current page
function setActiveNavLink() {
    const currentPath = window.location.pathname;
    const currentPage = currentPath.substring(currentPath.lastIndexOf('/') + 1);

    // Remove active class from all nav links
    document.querySelectorAll('.nav-link, .list-group-item').forEach(link => {
        link.classList.remove('active');
    });

    // Add active class to current page link based on the actual page
    if (currentPage === 'index.html' || currentPage === '') {
        document.querySelector('a[href*="index.html"]')?.classList.add('active');
    } else if (currentPage.includes('edit-project')) {
        document.querySelector('a[href*="edit-project"]')?.classList.add('active');
    } else if (currentPage.includes('membership')) {
        document.querySelector('a[href*="membership"]')?.classList.add('active');
    } else if (currentPage.includes('profile')) {
        document.querySelector('a[href*="profile"]')?.classList.add('active');
    } else if (currentPage.includes('login')) {
        document.querySelector('a[href*="login"]')?.classList.add('active');
    } else if (currentPage.includes('signup')) {
        document.querySelector('a[href*="signup"]')?.classList.add('active');
    }
}

// Function to handle form submissions
function handleFormSubmit(formId, endpoint) {
    const form = document.getElementById(formId);
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            
            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Success:', data);
                // Handle success response
                if (data.success) {
                    alert('Operation successful!');
                    form.reset();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    }
}

// Function to display asset gallery
function loadAssetGallery() {
    // This would normally fetch assets from the server
    const assets = [
        { id: 1, name: 'hero-sprite.png', path: 'assets/uploads/hero-sprite.png', category: 'Character' },
        { id: 2, name: 'enemy-sprite.png', path: 'assets/uploads/enemy-sprite.png', category: 'Character' },
        { id: 3, name: 'background.png', path: 'assets/uploads/background.png', category: 'Environment' }
    ];
    
    const galleryContainer = document.getElementById('asset-gallery');
    if (galleryContainer) {
        let html = '<div class="row">';
        assets.forEach(asset => {
            html += `
                <div class="col-md-3 mb-3">
                    <div class="card h-100">
                        <img src="${asset.path}" class="card-img-top asset-thumbnail" alt="${asset.name}" style="height: 150px; object-fit: cover;">
                        <div class="card-body">
                            <h6 class="card-title">${asset.name}</h6>
                            <p class="card-text"><small class="text-muted">${asset.category}</small></p>
                            <button class="btn btn-sm btn-outline-primary" onclick="selectAsset(${asset.id})">Use Asset</button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteAsset(${asset.id})">Delete</button>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        galleryContainer.innerHTML = html;
    }
}

// Function to select an asset
function selectAsset(assetId) {
    console.log('Selected asset:', assetId);
    // Add logic for asset selection
}

// Function to delete an asset
function deleteAsset(assetId) {
    if (confirm('Are you sure you want to delete this asset?')) {
        console.log('Deleting asset:', assetId);
        // Add logic for asset deletion
        loadAssetGallery(); // Refresh gallery after deletion
    }
}

// Function to update character stats display
function updateCharacterStats() {
    const hp = document.getElementById('hp-slider').value;
    const attack = document.getElementById('attack-slider').value;
    const speed = document.getElementById('speed-slider').value;
    
    document.getElementById('hp-value').textContent = hp;
    document.getElementById('attack-value').textContent = attack;
    document.getElementById('speed-value').textContent = speed;
    
    // Calculate and display stat total
    const total = parseInt(hp) + parseInt(attack) + parseInt(speed);
    document.getElementById('stat-total').textContent = `Total: ${total}`;
}

// Function to initialize character stat sliders
function initCharacterStatSliders() {
    const sliders = document.querySelectorAll('.stat-slider');
    sliders.forEach(slider => {
        slider.addEventListener('input', updateCharacterStats);
    });
    
    // Initial update
    updateCharacterStats();
}

// Function to create a new story node
function createStoryNode() {
    const title = document.getElementById('node-title').value;
    const content = document.getElementById('node-content').value;
    
    if (title && content) {
        // In a real implementation, this would add the node to the canvas
        console.log('Creating node:', { title, content });
        
        // Reset form
        document.getElementById('node-title').value = '';
        document.getElementById('node-content').value = '';
        
        alert('Story node created successfully! (Mock implementation)');
    } else {
        alert('Please fill in both title and content for the story node.');
    }
}

// Function to load story nodes
function loadStoryNodes() {
    // Mock story nodes for demonstration
    const nodes = [
        { id: 1, title: 'Start', content: 'Player starts the game', x: 100, y: 100 },
        { id: 2, title: 'Choice 1', content: 'Player makes first decision', x: 250, y: 200 },
        { id: 3, title: 'Choice 2', content: 'Player makes second decision', x: 400, y: 100 }
    ];
    
    console.log('Loading story nodes:', nodes);
    // In a real implementation, this would render the nodes on the canvas
}

// Function to handle file uploads
function handleFileUpload(inputId, endpoint) {
    const input = document.getElementById(inputId);
    if (input) {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('file', file);
                
                fetch(endpoint, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('File uploaded successfully!');
                        loadAssetGallery(); // Refresh gallery after upload
                    } else {
                        alert('Upload failed: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    alert('Upload failed. Please try again.');
                });
            }
        });
    }
}

// Function to handle PDF export
function exportToPDF() {
    alert('PDF Export initiated! (Mock implementation)');
    // In a real implementation, this would generate a PDF using a library like jsPDF
}

// Initialize functions when the page loads
document.addEventListener('DOMContentLoaded', function() {
    loadNav();
    setActiveNavLink();
    setupSmoothScrolling();

    // Initialize specific functionality based on page
    if (document.getElementById('asset-gallery')) {
        loadAssetGallery();
    }

    if (document.querySelectorAll('.stat-slider').length > 0) {
        initCharacterStatSliders();
    }

    if (document.getElementById('node-create-btn')) {
        document.getElementById('node-create-btn').addEventListener('click', createStoryNode);
    }

    if (document.getElementById('story-editor')) {
        loadStoryNodes();
    }

    if (document.getElementById('pdf-export-btn')) {
        document.getElementById('pdf-export-btn').addEventListener('click', exportToPDF);
    }

    // Initialize file upload handlers
    handleFileUpload('asset-upload', 'php/upload.php');
});