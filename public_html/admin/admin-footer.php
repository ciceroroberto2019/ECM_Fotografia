<?php
// admin/admin-footer.php
?>
        </main> </div> <script src="../js/lib/jquery.min.js?v=<?php echo SITE_VERSION; ?>"></script>
    
    <script src="../js/lib/jquery.magnific-popup.min.js?v=<?php echo SITE_VERSION; ?>"></script>

    <script>
        // Regra 8: Exemplo de uso real
        // Inicializa o Lightbox na galeria de visualização do admin
        // Boa prática (Regra 4): $(document).ready garante que o DOM carregou
        $(document).ready(function() {
            var $gallery = $('.popup-gallery');
            
            if ($gallery.length) {
                $gallery.magnificPopup({
                    delegate: 'a.admin-foto-link', // Alvo: os links que adicionamos
                    type: 'image',
                    gallery: {
                        enabled: true // Habilita setas de navegação
                    },
                    image: {
                        titleSrc: 'title' // Pega o nome do arquivo do atributo 'title'
                    }
                });
            }
        });
    </script>
</body>
</html>