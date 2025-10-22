<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliação de Música — Corrigido</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a1a1a;
            color: #e0e0e0;
            padding: 20px;
        }

        .container { max-width: 1200px; margin: 0 auto; }

        .music-header {
            background: linear-gradient(135deg, #2d1b4e 0%, #1a1a1a 100%);
            border-radius: 12px; padding: 30px; margin-bottom: 30px; border: 1px solid #3d2b5f;
        }

        .music-info { display:flex; gap:20px; align-items:center; margin-bottom:20px; }

        .album-cover {
            width:120px; height:120px; background:linear-gradient(135deg,#8b5cf6,#ec4899);
            border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:48px; color:#fff;
        }

        .music-details h1 { font-size:28px; margin-bottom:8px; color:#fff; }
        .music-details p { color:#a0a0a0; font-size:16px; }

        .rating-summary { display:flex; align-items:center; gap:30px; padding:20px; background:rgba(139,92,246,0.06); border-radius:8px; border:1px solid rgba(139,92,246,0.18); }
        .avg-rating { text-align:center; }
        .avg-rating .number { font-size:48px; font-weight:bold; color:#f97316; }
        .stars { display:flex; gap:4px; margin:8px 0; }
        .star { color:#f97316; font-size:20px; }
        .star.empty { color:#4a4a4a; }
        .total-reviews { color:#a0a0a0; font-size:14px; }

        .content-grid { display:grid; grid-template-columns:1fr 1fr; gap:30px; margin-bottom:30px; }
        .chart-section, .reviews-section { background:#242424; border-radius:12px; padding:25px; border:1px solid #333; }
        h2 { color:#8b5cf6; margin-bottom:20px; font-size:20px; }

        .chart-bars { display:flex; flex-direction:column; gap:15px; }
        .bar-item { display:flex; align-items:center; gap:12px; }
        .genre-name { width:90px; font-size:14px; }
        .bar-container { flex:1; background:#1a1a1a; border-radius:6px; height:30px; position:relative; overflow:hidden; }
        .bar-fill { height:100%; background:linear-gradient(90deg,#8b5cf6,#f97316); border-radius:6px; transition:width 0.5s ease; display:flex; align-items:center; justify-content:flex-end; padding-right:10px; color:white; font-size:12px; font-weight:bold; }

        .review-card { background:#2a2a2a; border-radius:8px; padding:20px; margin-bottom:15px; border:1px solid #3a3a3a; position:relative; }
        .review-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
        .review-user { font-weight:bold; color:#fff; }
        .review-date { color:#888; font-size:12px; }
        .review-rating { display:flex; gap:3px; margin-bottom:10px; }
        .review-comment { color:#d0d0d0; line-height:1.5; margin-bottom:15px; }
        .review-actions { display:flex; gap:10px; }

        .btn { padding:8px 16px; border:none; border-radius:6px; cursor:pointer; font-size:14px; transition:all 0.2s; display:flex; align-items:center; gap:6px; }
        .btn-edit { background:#8b5cf6; color:white; }
        .btn-delete { background:#ef4444; color:white; }
        .btn-save { background:#f97316; color:white; }
        .btn-cancel { background:#4a4a4a; color:white; }

        .edit-form { margin-top:15px; }
        .edit-form textarea { width:100%; background:#1a1a1a; border:1px solid #4a4a4a; border-radius:6px; padding:12px; color:#e0e0e0; font-family:inherit; resize:vertical; margin-bottom:10px; }
        .edit-form textarea:focus { outline:none; border-color:#8b5cf6; }

        .star-rating { display:flex; gap:5px; margin-bottom:15px; align-items:center; }
        .star-rating input { display:none; }
        .star-rating label { cursor:pointer; font-size:24px; color:#4a4a4a; }
        .star-rating label.selected { color:#f97316; }

        .hidden { display:none; }

        @media (max-width:768px) {
            .content-grid { grid-template-columns:1fr; }
            .music-info { flex-direction:column; text-align:center; }
            .rating-summary { flex-direction:column; }
        }

        /* fadeOut animation used on delete */
        @keyframes fadeOut { from { opacity:1; transform:scale(1); } to { opacity:0; transform:scale(0.9); } }
    </style>
</head>
<body>
    <div class="container">
        <header class="music-header">
            <div class="music-info">
                <div class="album-cover">♪</div>
                <div class="music-details">
                    <h1>Título da Música - Artista</h1>
                    <p>Álbum • 2025 • 3:42</p>
                    <div class="rating-summary" style="margin-top:12px;">
                        <div class="avg-rating">
                            <div class="number" id="avg-number">4.2</div>
                            <div class="stars" id="avg-stars">★★★★☆</div>
                            <div class="total-reviews" id="total-reviews">125 avaliações</div>
                        </div>
                        <div>
                            <div style="font-size:14px;color:#cfcfcf;">Gêneros</div>
                            <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap">
                                <span style="background:#111;background:rgba(255,255,255,0.03);padding:6px 10px;border-radius:6px;font-size:13px">Eletrônica</span>
                                <span style="background:rgba(255,255,255,0.03);padding:6px 10px;border-radius:6px;font-size:13px">Pop</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="content-grid">
            <section class="chart-section">
                <h2>Distribuição de notas</h2>
                <div class="chart-bars" id="chart-bars">
                    <!-- barras serão preenchidas por JS -->
                    <div class="bar-item">
                        <div class="genre-name">5 estrelas</div>
                        <div class="bar-container"><div class="bar-fill" style="width:65%">65%</div></div>
                    </div>
                    <div class="bar-item">
                        <div class="genre-name">4 estrelas</div>
                        <div class="bar-container"><div class="bar-fill" style="width:20%">20%</div></div>
                    </div>
                    <div class="bar-item">
                        <div class="genre-name">3 estrelas</div>
                        <div class="bar-container"><div class="bar-fill" style="width:8%">8%</div></div>
                    </div>
                    <div class="bar-item">
                        <div class="genre-name">2 estrelas</div>
                        <div class="bar-container"><div class="bar-fill" style="width:4%">4%</div></div>
                    </div>
                    <div class="bar-item">
                        <div class="genre-name">1 estrela</div>
                        <div class="bar-container"><div class="bar-fill" style="width:3%">3%</div></div>
                    </div>
                </div>
            </section>

            <section class="reviews-section">
                <h2>Avaliações</h2>

                <!-- Review #1 -->
                <article class="review-card" id="review-1">
                    <div class="review-header">
                        <div>
                            <div class="review-user">João</div>
                            <div class="review-date">03 de outubro de 2025</div>
                        </div>
                        <div class="review-actions">
                            <button class="btn btn-edit" onclick="editReview(1)">Editar</button>
                            <button class="btn btn-delete" onclick="deleteReview(1)">Excluir</button>
                        </div>
                    </div>

                    <div class="review-rating" aria-hidden="true">
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star empty">★</span>
                    </div>

                    <div class="review-content">
                        <p class="review-comment">Curti a produção e a voz do artista — ótima mistura de sintetizadores e bateria.</p>
                    </div>

                    <!-- Formulário de edição (inicialmente escondido) -->
                    <form class="edit-form hidden" id="edit-form-1" onsubmit="event.preventDefault(); saveReview(1);">
                        <div class="star-rating" id="star-rating-1">
                            <input type="radio" id="r1-1" name="rating-1" value="1"><label for="r1-1" data-value="1">★</label>
                            <input type="radio" id="r1-2" name="rating-1" value="2"><label for="r1-2" data-value="2">★</label>
                            <input type="radio" id="r1-3" name="rating-1" value="3"><label for="r1-3" data-value="3">★</label>
                            <input type="radio" id="r1-4" name="rating-1" value="4"><label for="r1-4" data-value="4">★</label>
                            <input type="radio" id="r1-5" name="rating-1" value="5"><label for="r1-5" data-value="5">★</label>
                        </div>
                        <textarea id="comment-1" rows="3">Curti a produção e a voz do artista — ótima mistura de sintetizadores e bateria.</textarea>
                        <div style="display:flex;gap:8px;margin-top:8px;">
                            <button type="button" class="btn btn-save" onclick="saveReview(1)">Salvar</button>
                            <button type="button" class="btn btn-cancel" onclick="cancelEdit(1)">Cancelar</button>
                        </div>
                    </form>
                </article>

                <!-- Review #2 -->
                <article class="review-card" id="review-2">
                    <div class="review-header">
                        <div>
                            <div class="review-user">Mariana</div>
                            <div class="review-date">10 de outubro de 2025</div>
                        </div>
                        <div class="review-actions">
                            <button class="btn btn-edit" onclick="editReview(2)">Editar</button>
                            <button class="btn btn-delete" onclick="deleteReview(2)">Excluir</button>
                        </div>
                    </div>

                    <div class="review-rating" aria-hidden="true">
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star">★</span>
                        <span class="star empty">★</span>
                        <span class="star empty">★</span>
                    </div>

                    <div class="review-content">
                        <p class="review-comment">Boa faixa, mas senti falta de um refrão mais marcante.</p>
                    </div>

                    <form class="edit-form hidden" id="edit-form-2" onsubmit="event.preventDefault(); saveReview(2);">
                        <div class="star-rating" id="star-rating-2">
                            <input type="radio" id="r2-1" name="rating-2" value="1"><label for="r2-1" data-value="1">★</label>
                            <input type="radio" id="r2-2" name="rating-2" value="2"><label for="r2-2" data-value="2">★</label>
                            <input type="radio" id="r2-3" name="rating-2" value="3"><label for="r2-3" data-value="3">★</label>
                            <input type="radio" id="r2-4" name="rating-2" value="4"><label for="r2-4" data-value="4">★</label>
                            <input type="radio" id="r2-5" name="rating-2" value="5"><label for="r2-5" data-value="5">★</label>
                        </div>
                        <textarea id="comment-2" rows="3">Boa faixa, mas senti falta de um refrão mais marcante.</textarea>
                        <div style="display:flex;gap:8px;margin-top:8px;">
                            <button type="button" class="btn btn-save" onclick="saveReview(2)">Salvar</button>
                            <button type="button" class="btn btn-cancel" onclick="cancelEdit(2)">Cancelar</button>
                        </div>
                    </form>
                </article>

            </section>
        </main>
    </div>

    <script>
        // Mostra o formulário de edição
        function editReview(id) {
            const review = document.getElementById(`review-${id}`);
            if (!review) return;
            const content = review.querySelector('.review-content');
            const form = document.getElementById(`edit-form-${id}`);
            if (content) content.classList.add('hidden');
            if (form) {
                form.classList.remove('hidden');
                // sincroniza as estrelas do formulário com as que estão no display
                syncFormStarsFromDisplay(id);
            }
        }

        // Esconde o formulário e mostra o conteúdo
        function cancelEdit(id) {
            const review = document.getElementById(`review-${id}`);
            if (!review) return;
            const content = review.querySelector('.review-content');
            const form = document.getElementById(`edit-form-${id}`);
            if (form) form.classList.add('hidden');
            if (content) content.classList.remove('hidden');
        }

        // Salva a avaliação (local, simulando backend)
        function saveReview(id) {
            const form = document.getElementById(`edit-form-${id}`);
            if (!form) return;

            const commentEl = document.getElementById(`comment-${id}`);
            const comment = commentEl ? commentEl.value.trim() : '';

            // pega o rating (pode ser null)
            const checked = form.querySelector(`input[name="rating-${id}"]:checked`);
            const rating = checked ? parseInt(checked.value, 10) : 0;

            // atualiza a UI
            const review = document.getElementById(`review-${id}`);
            if (!review) return;

            const commentDiv = review.querySelector('.review-comment');
            const ratingDiv = review.querySelector('.review-rating');

            if (commentDiv) commentDiv.textContent = comment || '—';

            if (ratingDiv) {
                // reconstrói as estrelas com checagem segura
                let starsHtml = '';
                for (let i = 1; i <= 5; i++) {
                    starsHtml += `<span class="star${i <= rating ? '' : ' empty'}">★</span>`;
                }
                ratingDiv.innerHTML = starsHtml;
            }

            // fecha o formulário
            cancelEdit(id);

            // opcional: atualizar média e distribuição (não implementado aqui)
            alert('Avaliação atualizada com sucesso!');
        }

        // Exclui a avaliação
        function deleteReview(id) {
            if (!confirm('Tem certeza que deseja excluir esta avaliação?')) return;
            const review = document.getElementById(`review-${id}`);
            if (!review) return;
            review.style.animation = 'fadeOut 0.25s';
            setTimeout(() => review.remove(), 250);
        }

        // Função auxiliar: sincroniza os inputs do formulário com o estado atual mostrado (estrelas)
        function syncFormStarsFromDisplay(id) {
            const review = document.getElementById(`review-${id}`);
            if (!review) return;
            const ratingDiv = review.querySelector('.review-rating');
            if (!ratingDiv) return;

            // conta estrelas que não têm a classe 'empty'
            const stars = ratingDiv.querySelectorAll('.star');
            let current = 0;
            stars.forEach((s, idx) => {
                if (!s.classList.contains('empty')) current = idx + 1;
            });

            // marca o input correspondente no form
            const form = document.getElementById(`edit-form-${id}`);
            if (!form) return;
            const input = form.querySelector(`input[name="rating-${id}"][value="${current}"]`);
            if (input) input.checked = true;
            // atualiza visual das labels
            updateStarVisual(id, current);
        }

        // Atualiza visual das labels de estrela dentro do formulário
        function updateStarVisual(id, value) {
            const container = document.getElementById(`star-rating-${id}`);
            if (!container) return;
            const labels = container.querySelectorAll('label');
            labels.forEach(label => {
                const v = parseInt(label.getAttribute('data-value'), 10);
                if (v <= value) label.classList.add('selected'); else label.classList.remove('selected');
            });
        }

        // adiciona listeners para que ao clicar na label a visual seja atualizada
        function attachStarLabelListeners() {
            const starContainers = document.querySelectorAll('.star-rating');
            starContainers.forEach(container => {
                const id = container.id.replace('star-rating-', '');
                const inputs = container.querySelectorAll('input');
                inputs.forEach(inp => {
                    inp.addEventListener('change', () => {
                        const val = parseInt(inp.value, 10);
                        updateStarVisual(id, val);
                    });
                });

                // também faz hover para previsualizar
                const labels = container.querySelectorAll('label');
                labels.forEach(label => {
                    label.addEventListener('mouseenter', () => {
                        const v = parseInt(label.getAttribute('data-value'), 10);
                        updateStarVisual(id, v);
                    });
                    label.addEventListener('mouseleave', () => {
                        // restaura para o valor marcado
                        const checked = container.querySelector('input:checked');
                        const val = checked ? parseInt(checked.value, 10) : 0;
                        updateStarVisual(id, val);
                    });
                });
            });
        }

        // inicialização
        document.addEventListener('DOMContentLoaded', () => {
            attachStarLabelListeners();
            // inicializa displays (ex.: média e barras) se quiser automatizar
        });
    </script>
</body>
</html>
