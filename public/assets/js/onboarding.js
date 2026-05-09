document.addEventListener('DOMContentLoaded', () => {
  const forms = document.querySelectorAll('[data-onboarding-add-friend]');
  const feedback = document.getElementById('onboarding-friends-feedback');
  const cardsContainer = document.querySelector('[data-onboarding-friends]');

  if (!forms.length || !feedback || !cardsContainer) {
    return;
  }

  const renderFeedback = (message, type) => {
    feedback.innerHTML = `<div class="alert alert-${type} shadow-sm mb-0" role="alert">${message}</div>`;
  };

  const renderEmptyStateIfNeeded = () => {
    if (cardsContainer.querySelectorAll('[data-friend-card]').length === 0) {
      cardsContainer.innerHTML = '<div class="empty-state">Na razie nie ma nikogo nowego do dodania.</div>';
    }
  };

  forms.forEach((form) => {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();

      const button = form.querySelector('button[type="submit"]');
      const card = form.closest('[data-friend-card]');
      const originalLabel = button ? button.textContent : 'Dodaj';

      if (button) {
        button.disabled = true;
        button.textContent = 'Wysylanie...';
      }

      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        });

        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
          throw new Error('Serwer zwrocil niepoprawna odpowiedz. Odswiez strone i sprobuj ponownie.');
        }

        const payload = await response.json();

        if (!response.ok || !payload.success) {
          throw new Error(payload.message || 'Nie udalo sie wyslac zaproszenia.');
        }

        renderFeedback(payload.message, 'success');

        if (card) {
          card.remove();
          renderEmptyStateIfNeeded();
        }
      } catch (error) {
        renderFeedback(error.message || 'Nie udalo sie wyslac zaproszenia.', 'warning');

        if (button) {
          button.disabled = false;
          button.textContent = originalLabel;
        }

        return;
      }

      if (button) {
        button.disabled = true;
        button.textContent = 'Wyslano';
        button.classList.remove('btn-outline-primary');
        button.classList.add('btn-success');
      }
    });
  });
});

