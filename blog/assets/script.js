document.querySelectorAll("article").forEach(article => {
  article.addEventListener("mouseenter", () => {
    article.style.borderColor = "#ff00ff";
  });
  article.addEventListener("mouseleave", () => {
    article.style.borderColor = "#00ffff";
  });
});

// contact form guard (only attach if exists)
const contactForm = document.getElementById("contactForm");
if (contactForm) {
  contactForm.addEventListener("submit", function(e) {
    const name = this.name.value.trim();
    const email = this.email.value.trim();
    const message = this.message.value.trim();

    if (name.length < 2 || message.length < 10) {
      alert("Lütfen geçerli bir ad ve mesaj girin.");
      e.preventDefault();
    }
  });
}

// Account dropdown toggle
document.addEventListener('DOMContentLoaded', function() {
  const accountBtn = document.getElementById('accountBtn');
  const accountDropdown = document.getElementById('accountDropdown');

  if (accountBtn && accountDropdown) {
    accountBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      const open = accountDropdown.getAttribute('aria-hidden') === 'false';
      accountDropdown.setAttribute('aria-hidden', String(!open));
      accountDropdown.classList.toggle('open', !open);
    });

    // close on outside click
    document.addEventListener('click', function() {
      if (accountDropdown.getAttribute('aria-hidden') === 'false') {
        accountDropdown.setAttribute('aria-hidden', 'true');
        accountDropdown.classList.remove('open');
      }
    });

    // prevent closing when clicking inside dropdown
    accountDropdown.addEventListener('click', function(e){ e.stopPropagation(); });
  }
});

// Guest account dropdown (links to login/register)
// (guest dropdown removed) 