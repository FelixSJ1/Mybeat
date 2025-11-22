<?php
// app/Views/painelmusic.php
// Variáveis esperadas: $music, $ratingStats, $distribution, $reviews
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Estatísticas — <?= htmlspecialchars($music['titulo'] ?? $music['titulo_album'] ?? 'Música/Álbum') ?></title>
    <link rel="stylesheet" href="/Mybeat/public/css/painelmusic.css">
</head>
<body>
    <div class="wrap">
        <header class="hero">
            <div class="cover-area">
                <div class="cover">
                    <?php if (!empty($music['capa_album_url'])): ?>
                        <img src="<?= htmlspecialchars($music['capa_album_url']) ?>" alt="Capa">
                    <?php else: ?>
                        <div class="no-cover"><?= htmlspecialchars(substr($music['titulo'] ?? $music['titulo_album'] ?? '-',0,2)) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="head-info">
                <h1><?= htmlspecialchars($music['titulo'] ?? $music['titulo_album'] ?? '-') ?> <span class="artist"> - <?= htmlspecialchars($music['nome_artista'] ?? '-') ?></span></h1>
                <div class="meta">
                    <?php if (!empty($music['tipo_album'])): ?><?= htmlspecialchars($music['tipo_album']) ?> • <?php endif; ?>
                    <?= !empty($music['data_lancamento']) ? htmlspecialchars($music['data_lancamento']) : '' ?>
                    <?php if (!empty($music['duracao_segundos'])): ?> • <?= intval($music['duracao_segundos']/60) . ':' . sprintf('%02d', $music['duracao_segundos']%60) ?><?php endif; ?>
                </div>

                <div class="rating-box">
                    <div class="rating-number"><?= number_format($ratingStats['media_nota'] ?? 0, 1) ?></div>
                    <div class="rating-stars">
                        <?php
                        $avg = round(($ratingStats['media_nota'] ?? 0));
                        for ($i=1;$i<=5;$i++){
                            echo $i<= $avg ? '<span class="star filled">★</span>' : '<span class="star">☆</span>';
                        }
                        ?>
                        <div class="rating-count"><?= (int)($ratingStats['total_avaliacoes'] ?? 0) ?> avaliações</div>
                    </div>
                    <div class="tags">
                        <?php
                        $generos = $music['genero_album'] ?? $music['genero'] ?? '';
                        if (!empty($generos)){
                            $garr = array_map('trim', explode(',', $generos));
                            foreach ($garr as $g) {
                                echo '<span class="tag">'.htmlspecialchars($g).'</span>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </header>

        <main class="main-grid">
            <section class="panel distribution">
                <h3>Distribuição de notas</h3>
                <?php
                // distribution expected: keys '5'..'1' with ['count','percent']
                foreach ([5,4,3,2,1] as $s) {
                    $d = $distribution[(string)$s] ?? ['count'=>0,'percent'=>0];
                    $cnt = (int)$d['count'];
                    $pct = floatval($d['percent']);
                    $pctStr = number_format($pct,1,'.','');
                    ?>
                    <div class="bar-row">
                        <div class="bar-label"><?= $s ?> estrelas</div>
                        <div class="bar-wrap" title="<?= $pctStr ?>%">
                            <div class="bar-inner" style="width: <?= $pctStr ?>%"></div>
                        </div>
                        <div class="bar-right"><span class="pct-label"><?= $pctStr ?>%</span><span class="cnt-text"> <?= $cnt ?></span></div>
                    </div>
                <?php } ?>
            </section>

            <section class="panel reviews">
                <h3>Avaliações</h3>
                <?php if (!empty($_SESSION['flash_msg'])): ?>
                    <div class="flash"><?= htmlspecialchars($_SESSION['flash_msg']); unset($_SESSION['flash_msg']); ?></div>
                <?php endif; ?>

                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $r): ?>
                        <article class="review">
                            <div class="review-head">
                                <div class="review-author"><?= htmlspecialchars($r['nome_usuario'] ?? $r['nome_exibicao'] ?? 'Anônimo') ?></div>
                                <div class="review-date"><?= !empty($r['data_avaliacao'] ?? $r['data_criacao']) ? date('d \d\e F \d\e Y', strtotime($r['data_avaliacao'] ?? $r['data_criacao'])) : '' ?></div>
                            </div>
                            <div class="review-stars">
                                <?php
                                    $nota = round(floatval($r['nota'] ?? 0));
                                    for ($i=1;$i<=5;$i++){
                                        echo $i<= $nota ? '<span class="star filled">★</span>' : '<span class="star">☆</span>';
                                    }
                                ?>
                            </div>
                            <div class="review-text"><?= nl2br(htmlspecialchars($r['texto_review'] ?? '')) ?></div>

                            <div class="review-actions">
                                <?php if (isset($_SESSION['id_usuario']) && (int)$_SESSION['id_usuario'] === (int)($r['id_usuario'] ?? 0)): ?>
                                    <form method="POST" action="listar_giovana.php?controller=painelmusic&action=delete" onsubmit="return confirm('Excluir avaliação?')">
                                        <input type="hidden" name="id_avaliacao" value="<?= (int)($r['id_avaliacao'] ?? 0) ?>">
                                        <button class="btn btn-delete" type="submit">Excluir</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nenhuma avaliação ainda.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
