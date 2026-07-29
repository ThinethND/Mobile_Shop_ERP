# DezeStore AI Chatbot Setup

The chatbot is installed on the public frontend layout and posts to:

```text
POST /ai/chat
```

It supports Gemini API and OpenAI. Live product facts still come from the Laravel database, so prices, stock, product names, SKUs, and URLs are not guessed by the model.

## Required `.env`

Recommended free-tier Gemini setup:

```env
AI_PROVIDER=gemini
GEMINI_API_KEY=your_gemini_api_key_here
GEMINI_API_URL=https://generativelanguage.googleapis.com/v1beta
GEMINI_MODEL=gemini-2.5-flash
GEMINI_FILE_SEARCH_STORE_NAME=
GEMINI_MAX_OUTPUT_TOKENS=700
GEMINI_TEMPERATURE=0.2
GEMINI_TIMEOUT=30
```

Optional OpenAI setup:

```env
AI_PROVIDER=openai
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_MODEL=gpt-5.5
OPENAI_VECTOR_STORE_ID=
OPENAI_REASONING_EFFORT=low
OPENAI_VERBOSITY=low
OPENAI_MAX_OUTPUT_TOKENS=700
OPENAI_TIMEOUT=30
```

Then run:

```bash
php artisan config:clear
```

## Getting The Gemini API Key

1. Open https://aistudio.google.com/app/apikey
2. Sign in with your Google account.
3. Create a Gemini API key.
4. Put it in `.env` as `GEMINI_API_KEY`.
5. Keep `AI_PROVIDER=gemini`.

## Getting The OpenAI API Key

1. Open https://platform.openai.com/api-keys
2. Sign in to your OpenAI account.
3. Create a new secret key.
4. Put it in `.env` as `OPENAI_API_KEY`.
5. Do not commit the real key to Git.

## Optional RAG Stores

`OPENAI_VECTOR_STORE_ID` is optional. Leave it empty at first. The chatbot already retrieves live product data from MySQL.

`GEMINI_FILE_SEARCH_STORE_NAME` is also optional. Leave it empty at first. The chatbot reads the local knowledge file directly:

```text
docs/ai-knowledge/dezestore-knowledge-base.txt
```

Use a vector store later for policy/FAQ documents such as:

- delivery policy
- warranty policy
- return/refund policy
- store FAQ
- support/contact rules

Product price and stock should stay in MySQL because those values change often.

## DezeStore Knowledge File

The first knowledge-base file is here:

```text
docs/ai-knowledge/dezestore-knowledge-base.txt
```

For Gemini, you do not need to upload this file while it is small. The app includes it directly in the prompt.

If you later create a Gemini File Search store, copy its store name and add it to `.env`:

```env
GEMINI_FILE_SEARCH_STORE_NAME=fileSearchStores/your_store_name
```

For OpenAI, upload this file to an OpenAI vector store. After upload, copy the vector store ID and add it to `.env`:

```env
OPENAI_VECTOR_STORE_ID=vs_your_vector_store_id_here
```

Then run:

```bash
php artisan config:clear
```
