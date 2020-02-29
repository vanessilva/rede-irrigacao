// Sistema de irrigação para o trabalho de conclusão 
// do curso Redes de Computadores.
// Elaborado por Vanessa Aparecida da Silva


// Bibliotecas utilizadas
#include <DHT.h>
#include <WiFi.h>
#include <stdio.h>
#include <string.h>


const char* WIFI_SSID = "Projeto_Final"; // SSID do AP
const char* WIFI_PASS =  "ifsuldeminas"; // Senha do AP

const char* host = "192.168.1.101"; // Endereço do sinknode

// ====== PINAGEM ======= //
// Higrômmetro
    // VIN --> VCG
    // GND --> GND
    // A0  --> VP 
#define PIN_HIGR A0  // Definição do pino 0 analógico como de irrigação

// Sensor de temperatura e umidade do ar
    // Visão frontal do DHT11
    // 1 --> 3V3
    // 2 --> D23
    // 3 --> 
    // 4 --> GND

#define DHTPIN 23 // Definição do pino 4 como de irrigação 
#define DHTTYPE DHT11 // definição do tipo do sensor DHT como DHT11

#define LED_WIFI LED_BUILTIN // O LED indicador do Wifi é o mesmo embutido no ESP32

#define PIN_IRRIG 4 // Definição do pino 4 como de irrigação 


DHT dht(DHTPIN, DHTTYPE);


int tick = 0; // contador para verificação de tempos em tempos dos status wifi e sinknode
int minimo = 0; // define como 0 o valor mínimo inicial (irá se alterar com os dados obtidos do sinknode)
int maximo = 100; // define como 100 o valor máximo inicial (irá se alterar com os dados obtidos do sinknode)
bool chegarNoMaximo = false; // variável que irá se alternar para true para poder se chegar ao valor máximo recomendado de umidade.

/* Função para leitura do sensor higrômetro */
int lerSensorHigrometro(){  
    
    int valorLeitura;
    int valorConvertido;
    
    // Lê o valor do pino do higrômetro. O valor retornado vai de 0 a 4095.
    valorLeitura = analogRead(PIN_HIGR);
    
    // Conversão do valor para 0 a 100% 
    valorConvertido = constrain(valorLeitura,0,4095);
    valorConvertido = map(valorConvertido,0,4095,100,0);
    
    // Exibição dos valores via porta serial
    Serial.print("Leitura H: ");
    Serial.print(valorLeitura);
    Serial.print(" / Conversao H: ");
    Serial.println(valorConvertido);  
    
    // retorno da função
    return valorConvertido; 
}

/* Função para leitura do sensor de umidade de ar */
float lerSensorUmidadeAr(){
    float leituraUmidade = dht.readHumidity();
    return leituraUmidade;
}

/* Função para leitura do sensor de temperatura */
float lerSensorTemperatura(){    
    float leituraTemperatura = dht.readTemperature();
    return leituraTemperatura;
}

/* Função para conectar-se ao Access Point */
void conectarWifi(){
    // Desliga o LED indicador de conexão Wifi
    digitalWrite(LED_WIFI, LOW);
    
    // Enquanto o Wifi estiver desconectado, o LED irá piscar:
    while (WiFi.status() != WL_CONNECTED) {
      digitalWrite(LED_WIFI, HIGH);
      delay(500);
      digitalWrite(LED_WIFI, LOW);
      delay(500);
      digitalWrite(LED_WIFI, HIGH);
      delay(500);
      digitalWrite(LED_WIFI, LOW);
      delay(1000);
      Serial.println("Conectando a WiFi..");
    }
    // Liga o LED indicador de conexão Wifi
    digitalWrite(LED_WIFI, HIGH);      
    
    // Exibição dos dados de conexão via porta serial
    Serial.print("Conectado a WiFi! Endereco: ");
    Serial.println(WiFi.localIP());
  
}

void leituraDados(){
    // Leitura dos dados dos sensores
    int sensorHigrometro = lerSensorHigrometro();
    float sensorUmidade = lerSensorUmidadeAr();
    float sensorTemperatura = lerSensorTemperatura();
    
    // Exibição dos dados dos sensores via porta serial
    Serial.print("Higrometro: ");
    Serial.print(sensorHigrometro);
    Serial.print("% / Umidade: ");
    Serial.print(sensorUmidade);
    Serial.print(" / Temperatura: ");
    Serial.print(sensorTemperatura);
    Serial.println(" ºC");

    // Conexão HTTP ao sinknode
    WiFiClient client;
    const int httpPort = 80;
    if (!client.connect(host, httpPort)) {
        Serial.println("Falha ao conectar ao sinknode!");
        return;
    }
    
    // Montar a URL para envio de dados ao sinknode
    // Exemplo: /get_sensor.php?h=50&u=45&t=30
    String url = "/get_sensor.php?h=";
    url += sensorHigrometro;
    url += "&u=";
    url += sensorUmidade;
    url += "&t=";
    url += sensorTemperatura;  
    Serial.print("URL: ");
    Serial.println(url);

    // Método HTTP GET que envia os dados
    client.print(String("GET ") + url + " HTTP/1.1\r\n" +
                 "Host: " + host + "\r\n" +
                 "Connection: close\r\n\r\n");

    // Aguarda 5 segundos... se não responder, desiste da conexão    
    unsigned long timeout = millis();
    while (client.available() == 0) {
        if (millis() - timeout > 5000) {
            Serial.println("Falha ao enviar os dados ao sinknode (timeout)!");
            client.stop();
            return;
        }
    }

    // Caso chegue até aqui, os dados do sinknode estão prontos para serem lidos.
    /* Formato separado em colunas:
        <CABECALHO HTTP>|VALOR_MINIMO|VALOR_MAXIMO|
    */
    int contador_colunas = 1;    
    Serial.println("\n\n>>> Fazendo o armazenamento dos dados recebidos...");
    
    // Ler coluna a coluna
    while(client.available()) { 
        
        // Ler até o caracter pipe 
        String coluna = client.readStringUntil('|');
       
        switch (contador_colunas){
          case 1: // Ignorar a primeira coluna, pois é apenas o cabeçalho HTTP.
              Serial.print("Cabecalho HTTP: ");
              Serial.println(coluna);
              break;
          case 2: // Armazenar o valor MINIMO 
              
              Serial.print("Valor minimo atual: ");
              Serial.println(minimo);
              Serial.print("Valor recebido do sinknode: ");
              Serial.println(coluna);
              // Fazer a conversao e armazenar na variável mínimo              
              minimo = coluna.toInt();
              Serial.print("Novo valor minimo armazenado: ");
              Serial.println(minimo);
              break;
              
          case 3: // Armazenar o valor MAXIMO 
              Serial.print("Valor maximo atual: ");
              Serial.println(maximo);
              Serial.print("Valor recebido do sinknode: ");
              Serial.println(coluna);
              // fazer a conversao e armazenar na variável maximo
              maximo = coluna.toInt();
              Serial.print("Valor maximo armazenado: ");
              Serial.println(maximo);
              break;            
        }
        // Aumentar o contador para ler a próxima coluna
        contador_colunas++;
    }
    
    
}

void verificarIrrigacao(){
    int sensorHigrometro = lerSensorHigrometro();
    /* Se:
     *  o valor do higrômetro for menor que o mínimo
     *  OU valor do higrômetro for menor que o maximo E valor do higrômetro tem que atingir ao maximo:
     */    
    if ((sensorHigrometro < minimo) || (sensorHigrometro < maximo && chegarNoMaximo == true)){
       chegarNoMaximo = true; // como chegou abaixo do mínimo, essa variável vai fazer que com que chegue ao máximo
       Serial.println("Acionando sistema de irrigacao...");
       digitalWrite(PIN_IRRIG, HIGH); // liga a irrigação
       delay(1000); // dorme por 1 segundo
       Serial.println("Desligando sistema de irrigacao...");
       digitalWrite(PIN_IRRIG, LOW); // desliga a irrigação
       
    } else {
       Serial.println("Umidade do solo nos padroes normais.");       
       chegarNoMaximo = false; // já está nos padrões normais. Não é preciso chegar mais no máximo
    }
    
}

// Função que roda quando liga ou reseta o ESP32 (roda uma vez).
void setup() {
    
    // Inicia a porta serial com velocidade em 9600 bit/s
    Serial.begin(9600);
    
    // Define o pino do LED indicador para o Wifi como saída
    pinMode(LED_WIFI, OUTPUT);
    
    // Define o pino do irrigador como saída
    pinMode(PIN_IRRIG, OUTPUT);
    
    // Inicia o módulo sensor de temperatura e umidade
    dht.begin();
    
    // Inicia o módulo Wifi
    WiFi.begin(WIFI_SSID, WIFI_PASS); 
}


// Função que roda todo o código em loop (infinitamente).    
void loop() {	
    
    /* "tick" é uma variavel de contagem.
        Quando ela tem o valor zero, serão feitos: 
    */
    if(tick == 0){
        //  - a verificação do Wifi (fica tentando se conectar até que consiga)
        while (WiFi.status() != WL_CONNECTED) {
            conectarWifi();
        }
        /* - Fazer a leitura dos dados e os envia ao sinknode.
           O sinknode irá responder com o mínimo e máximo de irrigação.
        */
        leituraDados();
    }
    
    // Função que verifica se necessita irrigar ou não.
    verificarIrrigacao();    
    
    // Incrementar a variável tick    
    tick++;    
    if (tick >= 6){ 
      tick = 0;
    }  
    
    // Dormir por 10 segundos	
    delay(10000);    
} 
