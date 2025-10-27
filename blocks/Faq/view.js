import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save( { attributes } ) {
    const blockProps = useBlockProps.save();
    const { title, faqs } = attributes;

    const validFaqs = faqs.filter(
        ( faq ) => faq.question.trim() !== '' && faq.answer.trim() !== ''
    );

    return (
        <div { ...blockProps }>
            { title && (
                <RichText.Content
                    tagName="h3"
                    className="bb-faq-main-title"
                    value={ title }
                />
            ) }
            { validFaqs.length > 0 && (
                <div className="bb-faq-list">
                    { validFaqs.map( ( faq, index ) => (
                        <div key={ index } className="bb-faq-item">
                            <RichText.Content
                                tagName="p"
                                className="bb-faq-question"
                                value={ faq.question }
                            />
                            <RichText.Content
                                tagName="div"
                                className="bb-faq-answer"
                                value={ faq.answer }
                            />
                        </div>
                    ) ) }
                </div>
            ) }
        </div>
    );
}
