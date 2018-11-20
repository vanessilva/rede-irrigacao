#include <DHT.h>

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

void setup() {
  Serial.begin(9600);
  dht.begin();
}

void loop() { 
  int leituraUmidade = lerSensorUmidadeAr();  
  int leituraTemperatura = lerSensorTemperatura();
  int leituraHigrometro = lerSensorHigrometro();
  Serial.print("Higrômetro: ");
  Serial.print(leituraHigrometro);
  Serial.print("% / Umidade: ");
  Serial.print(leituraUmidade);
  Serial.print("% / Temperatura: ");
  Serial.println(leituraTemperatura);
  delay(1000);
}
