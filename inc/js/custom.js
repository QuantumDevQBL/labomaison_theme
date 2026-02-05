document.addEventListener('DOMContentLoaded', function () {
    console.log('custom.js chargé');


    const toggleBtn = document.querySelector('.toggle-filters-button');
    const panel = document.querySelector('.filters-panel');
    const closeBtn = document.querySelector('.close-filters-button');

    if (!toggleBtn || !panel) return;
	


    toggleBtn.addEventListener('click', function () {
        const isOpen = panel.classList.toggle('open');

        // Pour mobile : bloquer le scroll de fond
        if (window.innerWidth < 768) {
            document.body.classList.toggle('filters-open', isOpen);
        }
    });

    // FERMETURE
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            panel.classList.remove('open');
            document.body.classList.remove('filters-open');
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && panel.classList.contains('open')) {
            panel.classList.remove('open');
            document.body.classList.remove('filters-open');
        }
    });

    // Sélectionne tous les éléments <p> dans le conteneur avec la classe logo_brand_container
    const paragraphs = document.querySelectorAll('.logo_brand_container p');

    paragraphs.forEach(paragraph => {
        // Si le paragraphe est vide, le supprimer
        if (paragraph.textContent.trim() === '') {
            paragraph.remove();
        }
    });

    // Ajout de checkboxes personnalisées aux cartes WP Grid Builder
    const addCustomCheckboxesToCards = () => {
        document.querySelectorAll('.wpgb-grid-20 .wpgb-card').forEach(card => {
            const postIdMatch = card.className.match(/wpgb-post-(\d+)/);
            if (postIdMatch) {
                const checkboxContainer = createCheckboxContainer(postIdMatch[1]);
                const cardInner = card.querySelector('.wpgb-card-inner');
                // Utilisation d'une vérification conditionnelle standard
                if (cardInner) {
                    cardInner.insertBefore(checkboxContainer, cardInner.firstChild);
                }
            }
        });
    };


	// Script discret de log des Layout Shifts, sans impact visuel
if ('PerformanceObserver' in window) {
  console.log('[CLS] Observer actif');
  const observer = new PerformanceObserver((list) => {
    list.getEntries().forEach((entry) => {
      if (entry.value > 0) {
        console.groupCollapsed(`[CLS] Layout shift détecté (score: ${entry.value.toFixed(4)})`);
        console.log('⏱️ Timestamp:', entry.startTime.toFixed(2), 'ms');
        console.log('ℹ️ Raw Entry:', entry);
        if (entry.sources && entry.sources.length) {
          entry.sources.forEach((source, i) => {
            console.log(`🔧 Élément ${i + 1}:`, source.node);
            console.log('⛳ Ancienne position:', source.previousRect);
            console.log('🎯 Nouvelle position:', source.currentRect);
          });
        } else {
          console.log('⚠️ Aucune source identifiable (entry.sources vide)');
        }
        console.groupEnd();
      }
    });
  });

  observer.observe({ type: 'layout-shift', buffered: true });
}


    // Création d'un conteneur de checkbox
    const createCheckboxContainer = (postId) => {
        const container = document.createElement('div');
        container.className = 'custom-checkbox-container';
        container.style.cssText = 'position: absolute; top: 10px; left: 10px;';

        const checkbox = document.createElement('input');
        Object.assign(checkbox, { type: 'checkbox', id: `select-${postId}`, name: 'selected_posts[]', value: postId, className: 'custom-checkbox' });

        const label = document.createElement('label');
        label.htmlFor = `select-${postId}`;
        label.textContent = 'Sélectionner';

        container.append(checkbox, label);
        return container;
    };

    // Gestion des changements sur les checkboxes et mise à jour des champs cachés
    const handleCheckboxChanges = () => {
        document.querySelectorAll('.custom-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const checkedBoxes = document.querySelectorAll('.custom-checkbox:checked');
                updateHiddenFieldsWithSelectedPosts(checkedBoxes);
                limitSelectionToTwo(checkedBoxes, checkbox);
            });
        });
    };

    const updateHiddenFieldsWithSelectedPosts = (checkedBoxes) => {
        const fields = [document.getElementById('selectedTest1'), document.getElementById('selectedTest2')];
        fields.forEach((field, index) => field.value = checkedBoxes[index]?.value || '');
    };

    const limitSelectionToTwo = (checkedBoxes, currentCheckbox) => {
        if (checkedBoxes.length > 2) {
            alert("Veuillez sélectionner exactement deux produits pour comparer.");
            currentCheckbox.checked = false;
        }
    };

    // Mise à jour des boutons de navigation de la pagination
    const updateNavigationButtons = () => {
        document.querySelectorAll('.wpgb-pagination a').forEach(link => {
            if (link.textContent.trim() === '>' || link.textContent.trim() === '<') {
                link.classList.add('navigation-button');
            }
        });
    };

    // Observation des changements dans le DOM pour les boutons de navigation
    const observePaginationChanges = () => {
        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                if (mutation.type === 'childList') {
                    updateNavigationButtons();
                }
            });
        });

        const targetNode = document.querySelector('.wpgb-pagination');
        if (targetNode) {
            observer.observe(targetNode, { childList: true, subtree: true });
        } else {
            console.warn("Le nœud cible pour la pagination n'a pas été trouvé. L'observer n'a pas été démarré.");
        }
    };

    // Exécution des fonctions initiales
    addCustomCheckboxesToCards();
    handleCheckboxChanges();
    updateNavigationButtons();
    observePaginationChanges();
});
