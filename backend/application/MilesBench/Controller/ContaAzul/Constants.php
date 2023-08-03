<?php

namespace MilesBench\Controller\ContaAzul;

class Constants {
    const url_sales = 'https://api.contaazul.com/v1/sales';
    const url_customers  = 'https://api.contaazul.com/v1/customers';

    const url_authorize = 'https://api.contaazul.com/auth/authorize?redirect_uri=http://52.70.119.195/cml-gestao&client_id=bZXG5i6Rjdai9CB2boje9xtZiABLlGqy&scope=sales&state=';

    const url_refresh = 'https://api.contaazul.com/oauth2/token?grant_type=refresh_token&refresh_token=';

    const url_oauth = 'https://api.contaazul.com/oauth2/token?grant_type=authorization_code&redirect_uri=http://52.70.119.195/cml-gestao&code=';

    const redirect_uri = 'http://52.70.119.195/cml-gestao';

    const client_id = 'bZXG5i6Rjdai9CB2boje9xtZiABLlGqy';
    const client_secret = 'NmKVqTW4NCqwus7ZSX12UgJqo8Z22qIE';
}
