// cliente-script.js (VERSÃO MESTRE COM DOWNLOAD)

$(document).ready(function() {
    
    const galeria = document.querySelector('.popup-gallery');
    const statusProjeto = galeria ? galeria.getAttribute('data-status') : null;

    // --- 1. Inicializar Magnific Popup (SEMPRE ATIVO) ---
    $('.popup-gallery').magnificPopup({
        delegate: 'a', 
        type: 'image',
        gallery: {
            enabled: true,
            navigateByImgClick: true,
            preload: [0, 1]
        },
        markup: '<div class="mfp-figure">'+
                    '<div class="mfp-close"></div>'+
                    '<div class="mfp-img"></div>'+
                    '<div class="mfp-bottom-bar">'+
                        '<div class="mfp-title"></div>'+ 
                        '<div class="mfp-counter"></div>'+
                    '</div>'+
                '</div>',
        
        callbacks: {
            elementParse: function(item) {
                const idFoto = item.el.attr('data-id-foto');
                const idProjeto = item.el.attr('data-id-projeto');
                const isSelecionada = item.el.hasClass('selecionada');
                const tituloFoto = item.el.attr('data-titulo-foto');
                const urlFoto = item.el.attr('href'); 

                let bottomBarHtml = '';
                let botoesHtml = '';

                // --- LÓGICA DE BOTÕES (AGORA COM DOWNLOAD) ---
                if (statusProjeto === 'Em Seleção') {
                    let buttonClass = isSelecionada ? 'selecionada' : '';
                    let buttonText = isSelecionada ? '✔ FOTO SELECIONADA' : '＋ SELECIONAR FOTO';
                    
                    botoesHtml = '<button class="btn-selecionar-foto ' + buttonClass + '" ' +
                                 'data-id-foto="' + idFoto + '" ' +
                                 'data-id-projeto="' + idProjeto + '">' +
                                     buttonText +
                                 '</button>' +
                                 '<a href="' + urlFoto + '" download="' + tituloFoto + '" class="btn-download-foto">Baixar esta Foto</a>';
                } else {
                    botoesHtml = '<a href="' + urlFoto + '" download="' + tituloFoto + '" class="btn-download-foto" style="width: 100%;">Baixar esta Foto</a>';
                }
                
                bottomBarHtml = '<h4>' + tituloFoto + '</h4>' +
                                '<div class="mfp-botoes-wrapper">' + 
                                    botoesHtml + 
                                    '<div class="alerta-mfp" style="display:none; width: 100%; grid-column: 1 / -1;"></div>' +
                                '</div>';
                
                item.title = bottomBarHtml;
            },
            
            open: function() {
                $('.mfp-content').on('click', '.btn-selecionar-foto', function() {
                    const btn = $(this); 
                    const idFoto = btn.data('id-foto');
                    const idProjeto = btn.data('id-projeto');
                    const alertaMfp = btn.closest('.mfp-botoes-wrapper').find('.alerta-mfp'); 

                    btn.prop('disabled', true).css('opacity', '0.6');
                    alertaMfp.hide().html('');

                    let formData = new FormData();
                    formData.append('id_foto', idFoto);
                    formData.append('id_projeto', idProjeto);

                    fetch('selecionar-foto.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        btn.prop('disabled', false).css('opacity', '1');
                        
                        const gridItem = $('a[data-id-foto="' + idFoto + '"]'); 
                        const contador = $('#contador-selecionadas'); 

                        if (data.status === 'sucesso') {
                            let contagemAtual = parseInt(contador.text());
                            if (data.acao === 'selecionada') {
                                btn.addClass('selecionada').text('✔ FOTO SELECIONADA');
                                gridItem.addClass('selecionada');
                                gridItem.find('.selection-overlay').html('✔');
                                if (contador.length > 0) {
                                    contador.text(contagemAtual + 1);
                                }
                            } else {
                                btn.removeClass('selecionada').text('＋ SELECIONAR FOTO');
                                gridItem.removeClass('selecionada');
                                gridItem.find('.selection-overlay').html('＋');
                                if (contador.length > 0) {
                                    contador.text(contagemAtual - 1);
                                }
                            }
                        } else {
                            alertaMfp.text('Erro: ' + data.mensagem).show();
                        }
                    })
                    .catch(error => {
                        btn.prop('disabled', false).css('opacity', '1');
                        alertaMfp.text('Erro de conexão.').show();
                    });
                });
            },

            afterChange: function() {
                if (this.currItem.title) {
                    this.content.find('.mfp-title').html(this.currItem.title);
                }
            }
        }
    });
        
    // --- LÓGICA DO BOTÃO DE CONFIRMAÇÃO (Existente) ---
    const btnConfirmar = document.getElementById('btn-confirmar-selecao');
    
    if (btnConfirmar) {
        btnConfirmar.addEventListener('click', function() {
            // (Código de confirmação que já funciona)
            const idProjeto = this.getAttribute('data-id-projeto');
            const contagem = parseInt(document.getElementById('contador-selecionadas').textContent);

            if (confirm('Tem certeza que deseja finalizar esta seleção com ' + contagem + ' fotos?\n\nEsta ação não poderá ser desfeita.')) {
                
                this.textContent = 'Enviando...';
                this.disabled = true;

                fetch('confirmar-selecao.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_projeto: idProjeto })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'sucesso') {
                        alert('Seleção enviada com sucesso! Você não poderá mais editar esta galeria.');
                        location.reload();
                    } else {
                        alert('Erro ao finalizar: ' + data.mensagem);
                        this.textContent = 'Confirmar e Enviar Seleção para o Álbum';
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    alert('Erro de conexão ao finalizar. Tente novamente.');
                    this.textContent = 'Confirmar e Enviar Seleção para o Álbum';
                    this.disabled = false;
                });
            }
        });
    }

});