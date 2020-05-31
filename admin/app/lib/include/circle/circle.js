// Execua a animação do circle quando o usuário pressionar a tecla enter
// 
// @author Artur Comunello
function onAtualizarCircle(event, value, circle_id)
{
	if (event.which == 13 || event.keyCode == 13)
	{
		atualizarCircle(value, circle_id);
	}

	return false;
}

//Aumenta ou diminui um contador
//
// @author Artur Comunello
function moverContador(contador, aumentar)
{
	if (aumentar)
	{
		return ++contador;
	}

	return --contador;
}

// Movimenta o circle (animação)
//
// @author Artur Comunello
function atualizarCircle(valor_escolhido, circle_id)
{
	var circle      = $('#'+circle_id);
	var max         = parseInt(circle.attr('max'));
	var atual       = parseInt(circle.attr('value'));
	valor_escolhido = parseInt(valor_escolhido);

	//Validaçẽs para executar a animação
	if ( isNaN(valor_escolhido) || valor_escolhido < 0 || valor_escolhido > max || valor_escolhido == atual)
	{
		return false;
	}

	//percentual que a ch escolhida representa do total da CH
	var percentual_escolhido = Math.round(valor_escolhido/max*100);
	var percentual_atual     = Math.round(atual/max*100);
	var aumentar_contador    = true;

	var circle_label = circle.find('.circle-label');

	//Aumentar ou diminuir o valor do circle
	if (atual > valor_escolhido)
	{
		aumentar_contador = false;
	}

	var intervalo = setInterval(
		function()
		{
			contador_percentual = Math.round(atual/max*100);

			circle.removeClass(function (index, css) {
				return (css.match (/\bp\S+/g) || []).join(' ');
			});

			circle.addClass('p'+contador_percentual);
			circle_label.html(atual);
			
			//Para o intervalo
			if (atual == valor_escolhido)
			{
				clearInterval(intervalo);
			}
			atual = moverContador(atual, aumentar_contador);
		},
		15
	);

	circle.attr('value', valor_escolhido);
}
