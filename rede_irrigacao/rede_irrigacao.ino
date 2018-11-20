#include <DHT.h>


// Sensor de temperatura e umidade do ar
// Visão frontal do DHT11
// 1 --> 3V3
// 2 --> D23
// 3 -->
// 4 --> GND
#define DHTPIN 23
#define DHTTYPE DHT11

DHT dht(DHTPIN, DHTTYPE);

void setup() {
  Serial.begin(9600);
  dht.begin();
}

void loop() { 
  int leituraUmidade = dht.readHumidity();  
  int leituraTemperatura = dht.readTemperature();
  Serial.print("Umidade: ");
  Serial.print(leituraUmidade);
  Serial.print("% / Temperatura: ");
  Serial.println(leituraTemperatura);
  delay(1000);
}
