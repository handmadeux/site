// jQuery.validator.addMethod('answercheck', function (value, element) {
//         return this.optional(element) || /^\bcat\b$/.test(value);
//     }, "type the correct answer -_-");

// validate contact form
$(function() {
    $('#contact').validate({
        rules: {
            name: {
                required: true,
                minlength: 2
            },
            email: {
                required: true,
                email: true
            },
            message: {
                required: true
            },
            // answer: {
            //     required: true,
            //     answercheck: true
            // }
        },
        messages: {
            name: {
                required: "vamos lá, nos fale seu nome.",
                minlength: "seu nome deve ter pelo menos 2 letras"
            },
            email: {
                required: "falta email, falta a mensagem"
            },
            message: {
                required: "um... é, você precisa nos escrever algo para enviar a mensagem.",
                minlength: "Isso é tudo?"
            },
            // answer: {
            //     required: "sorry, wrong answer!"
            // }
        },
        submitHandler: function(form) {
            $(form).ajaxSubmit({
                type:"POST",
                data: $(form).serialize(),
                url:"process.php",
                success: function() {
                    $('#contact :input').attr('disabled', 'disabled');
                    $('#contact').fadeTo( "slow", 0.15, function() {
                        $(this).find(':input').attr('disabled', 'disabled');
                        $(this).find('label').css('cursor','default');
                        $('#success').fadeIn();
                    });
                },
                error: function() {
                    $('#contact').fadeTo( "slow", 0.15, function() {
                        $('#error').fadeIn();
                    });
                }
            });
        }
    });
});