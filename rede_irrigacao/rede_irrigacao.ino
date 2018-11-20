#include <DHT.h>
#include <WiFi.h>

// ====== PINAGEM ======= //
// Higrômetro
// VIN --> VCG
// GND --> GND
// A0  --> VP - pino de leitura do higrômetro A0
#define SOIL_PIN A0

// Sensor de temperatura e umidade do ar
// Visão frontal do DHT11
// 1 --> 3V3
// 2 --> D23
// 3 -->
// 4 --> GND
#define DHTPIN 23
#define DHTTYPE DHT11


// Pino de irrigacao
#define IRRIG_PIN 4

// WIFI

const char* WIFI_SSID = "Nuxei";
const char* WIFI_PASS =  "caquinho";





DHT dht(DHTPIN, DHTTYPE);

int lerSensorHigrometro() {
  int valorLeitura;
  int valorConvertido;
  valorLeitura = analogRead(SOIL_PIN);
  valorConvertido = constrain(valorLeitura, 0, 4095);
  valorConvertido = map(valorConvertido, 0, 4095, 100, 0);
  Serial.print("Leitura H: ");
  Serial.print(valorLeitura);
  Serial.print(" / Conversao H: ");
  Serial.println(valorConvertido);
  return valorConvertido;
}

int lerSensorUmidadeAr() {
  int leituraUmidade = dht.readHumidity();
  return leituraUmidade;
}

int lerSensorTemperatura() {
  int leituraTemperatura = dht.readTemperature();
  return leituraTemperatura;
}

void conectarWifi() {
  digitalWrite(LED_BUILTIN, LOW);
  while (WiFi.status() != WL_CONNECTED) {
    digitalWrite(LED_BUILTIN, HIGH);
    delay(500);
    digitalWrite(LED_BUILTIN, LOW);
    delay(500);
    digitalWrite(LED_BUILTIN, HIGH);
    delay(500);
    digitalWrite(LED_BUILTIN, LOW);
    delay(1000);
    Serial.println("Conectando a WiFi..");
  }
  digitalWrite(LED_BUILTIN, HIGH);
  Serial.print("Rede sem fio conectada! Endereco: ");
  Serial.println(WiFi.localIP());
}

int buscarTempoIrrigacao(int higr, int umid, int temp) {
  /* TO-DO: aqui deve-se implementar a função que testa os valores
     para acionar o sistema de irrigação
     via webservice implementado no sinknode.
     O valor de retorno deve ser o tempo que 
     deve manter o sistema de irrigacao ligado.
  */
  // por enquanto, só se baseia em um valor padrão fixo do higrômetro ( < 25%).
  if (higr < 25){
    return 1500; // manter ligado por 1.5s
  } else {
    return 0;
  }  
}

void ligarIrrigacao(int tempoIrrigacao) {
  Serial.println("Ligando o sistema de irrigação...");
  digitalWrite(IRRIG_PIN, HIGH); // ligando sistema de irrigação
  delay(tempoIrrigacao);         // pausa 
  desligarIrrigacao();           // desligar para evitar excessos de consumo de água
}

void desligarIrrigacao() {
  Serial.println("Desligando o sistema de irrigação...");
  digitalWrite(IRRIG_PIN, LOW);
}

void setup() {
  Serial.begin(9600);
  dht.begin();  
  pinMode(LED_BUILTIN, OUTPUT);
  pinMode(IRRIG_PIN, OUTPUT);
  WiFi.begin(WIFI_SSID, WIFI_PASS);
}

void loop() {  
  conectarWifi();
  int leituraUmidade = lerSensorUmidadeAr();  
  int leituraTemperatura = lerSensorTemperatura();
  int leituraHigrometro = lerSensorHigrometro();
  Serial.print("Higrômetro: ");
  Serial.print(leituraHigrometro);
  Serial.print("% / Umidade: ");
  Serial.print(leituraUmidade);
  Serial.print("% / Temperatura: ");
  Serial.println(leituraTemperatura); 
  int tempoIrrigacao = buscarTempoIrrigacao(leituraHigrometro, leituraUmidade, leituraTemperatura);  
  if (tempoIrrigacao > 0) {
    ligarIrrigacao(tempoIrrigacao);
  } else {
    desligarIrrigacao();
  }  
  delay(3000);  
}
