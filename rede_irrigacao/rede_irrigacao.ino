#include <DHT.h>
#include <WiFi.h>
#include <stdio.h>
#include <string.h>


const char* WIFI_SSID = "Projeto_Final";
const char* WIFI_PASS =  "ifsuldeminas";

const char* host = "192.168.1.101";

// ====== PINAGEM ======= //
// Higrômmetro
    // VIN --> VCG
    // GND --> GND
    // A0  --> VP 
#define PIN_HIGR A0

// Sensor de temperatura e umidade do ar
    // Visão frontal do DHT11
    // 1 --> 3V3
    // 2 --> D23
    // 3 --> 
    // 4 --> GND
#define DHTPIN 23
#define DHTTYPE DHT11

#define LED_WIFI LED_BUILTIN

#define PIN_IRRIG 4


DHT dht(DHTPIN, DHTTYPE);


int tick = 0;
int minimo = 0;
int maximo = 100;
bool chegarNoMaximo = false;


int lerSensorHigrometro(){  
    int valorLeitura;
    int valorConvertido;
    valorLeitura = analogRead(PIN_HIGR);
    valorConvertido = constrain(valorLeitura,0,4095);
    valorConvertido = map(valorConvertido,0,4095,100,0);
    Serial.print("Leitura H: ");
    Serial.print(valorLeitura);
    Serial.print(" / Conversao H: ");
    Serial.println(valorConvertido);    
    return valorConvertido; 
}

float lerSensorUmidadeAr(){
    float leituraUmidade = dht.readHumidity();
    return leituraUmidade;
}

float lerSensorTemperatura(){    
    float leituraTemperatura = dht.readTemperature();
    return leituraTemperatura;
}

void conectarWifi(){
    digitalWrite(LED_WIFI, LOW);
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
    digitalWrite(LED_WIFI, HIGH);      
    Serial.print("Conectado a WiFi! Endereco: ");
    Serial.println(WiFi.localIP());
  
}

void leituraDados(){
    int sensorHigrometro = lerSensorHigrometro();
    float sensorUmidade = lerSensorUmidadeAr();
    float sensorTemperatura = lerSensorTemperatura();
    Serial.print("Higrometro: ");
    Serial.print(sensorHigrometro);
    Serial.print("% / Umidade: ");
    Serial.print(sensorUmidade);
    Serial.print(" / Temperatura: ");
    Serial.print(sensorTemperatura);
    Serial.println(" ºC");

    WiFiClient client;
    const int httpPort = 80;
    if (!client.connect(host, httpPort)) {
        Serial.println("connection failed");
        return;
    }
    // We now create a URI for the request
    String url = "/get_sensor.php?h=";
    url += sensorHigrometro;
    url += "&u=";
    url += sensorUmidade;
    url += "&t=";
    url += sensorTemperatura;
    // /get_sensor.php?h=50&u=45&t=30 //exemplo

    Serial.print("Requesting URL: ");
    Serial.println(url);

    // This will send the request to the server
    client.print(String("GET ") + url + " HTTP/1.1\r\n" +
                 "Host: " + host + "\r\n" +
                 "Connection: close\r\n\r\n");
    unsigned long timeout = millis();

    // Aguarda 5 segundos... se não responder, desiste da conexão
    while (client.available() == 0) {
        if (millis() - timeout > 5000) {
            Serial.println(">>> Client Timeout !");
            client.stop();
            return;
        }
    }

    int contador_colunas = 1;
    
    Serial.println("\n\n>>> Fazendo o armazenamento dos dados recebidos...");
    
    while(client.available()) { 
        String coluna = client.readStringUntil('|');
        
        switch (contador_colunas){
          case 1:
              Serial.print("Cabecalho HTTP: ");
              Serial.println(coluna);
              break;
          case 2: // valor MINIMO 
              Serial.print("Valor minimo atual: ");
              Serial.println(minimo);
              Serial.print("Valor recebido do sinknode: ");
              Serial.println(coluna);
              // fazer a conversao e armazenar na variável mínimo              
              minimo = coluna.toInt();
              Serial.print("Novo valor minimo armazenado: ");
              Serial.println(minimo);
              break;
              
          case 3: // valor MAXIMO 
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
        //Serial.print(coluna);
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

/*função que roda quando liga ou reseta o ESP32 (roda uma vez)*/ 
void setup() {
    Serial.begin(9600);
    pinMode(LED_WIFI, OUTPUT);
    pinMode(PIN_IRRIG, OUTPUT);
    dht.begin();
    WiFi.begin(WIFI_SSID, WIFI_PASS);
    
}

/*função que roda todo o código em loop (infinitamente)*/    
void loop() {  
    if(tick == 0){
      while (WiFi.status() != WL_CONNECTED) {
        conectarWifi();
      }    
      leituraDados(); // faz a leitura, o envio dos dados ao nó e também armazena o mínimo e máximo que foi recebido do sinknode
    }

    verificarIrrigacao();
        
    tick++;    
    if (tick >= 6){ 
      tick = 0;
    }    
    delay(10000);
    
}
